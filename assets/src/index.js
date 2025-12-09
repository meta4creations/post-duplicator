import './css/index.scss'
import { render, useState, useEffect, createRoot } from '@wordpress/element'
import DuplicateModal from './components/DuplicateModal'

function showSnackbar(message, type = 'error') {
  // Create the container
  const snackbar = document.createElement('div')
  snackbar.classList.add('my-snackbar', `my-snackbar--${type}`)
  snackbar.textContent = message

  // Append to body
  document.body.appendChild(snackbar)

  // Auto-remove after 3 seconds
  setTimeout(() => {
    snackbar.classList.add('my-snackbar--hide')
    snackbar.addEventListener('transitionend', () => snackbar.remove())
  }, 3000)
}

// React component to handle modal state
const DuplicatePostHandler = () => {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [currentPost, setCurrentPost] = useState(null)

  const handleDuplicate = async (settings) => {
    if (!currentPost) return

    const data = {
      original_id: currentPost.id,
      ...settings,
    }

    try {
      const response = await fetch(
        `${postDuplicatorVars.restUrl}duplicate-post`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': postDuplicatorVars.nonce,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
        }
      )

      if (!response.ok) {
        const errorJSON = await response.json()
        throw errorJSON
      }

      const result = await response.json()

      // If the server returned a new duplicate post ID, redirect with ?post-duplicated=...
      if (result.duplicate_id) {
        let loc = window.location.href
        if (loc.includes('?')) {
          loc += `&post-duplicated=${result.duplicate_id}`
        } else {
          loc += `?post-duplicated=${result.duplicate_id}`
        }
        window.location.href = loc
      }
    } catch (error) {
      console.error('Error duplicating post:', error.message)
      showSnackbar(`Error duplicating post: ${error.message}`, 'error')
      setIsModalOpen(false)
    }
  }

  // Set up click handler
  useEffect(() => {
    const handleClick = async (e) => {
      if (e.target.classList.contains('m4c-duplicate-post')) {
        e.preventDefault()

        const postId = e.target.getAttribute('data-postid')
        const postType = e.target.getAttribute('data-posttype') || 'post'

        // Fetch post data - use correct endpoint based on post type
        // WordPress REST API: posts -> /wp/v2/posts, pages -> /wp/v2/pages, custom types -> /wp/v2/{type}
        try {
          const baseUrl = postDuplicatorVars.restUrl.replace(
            'post-duplicator/v1/',
            'wp/v2/'
          )
          // Handle special case for pages (plural) vs other post types (singular)
          const endpointType = postType === 'page' ? 'pages' : postType === 'post' ? 'posts' : postType
          const endpoint = `${baseUrl}${endpointType}/${postId}`
          
          const response = await fetch(endpoint, {
            headers: {
              'X-WP-Nonce': postDuplicatorVars.nonce,
            },
          })

          if (!response.ok) {
            throw new Error('Failed to fetch post data')
          }

          const post = await response.json()
          
          // Fetch author data
          const authorResponse = await fetch(
            `${postDuplicatorVars.restUrl.replace(
              'post-duplicator/v1/',
              'wp/v2/'
            )}users/${post.author}`,
            {
              headers: {
                'X-WP-Nonce': postDuplicatorVars.nonce,
              },
            }
          )
          
          let authorName = 'Unknown Author'
          if (authorResponse.ok) {
            const authorData = await authorResponse.json()
            authorName = authorData.name
          }
          
          // Fetch taxonomy and custom meta data
          let taxonomies = []
          let customMeta = []
          
          try {
            const postDataResponse = await fetch(
              `${postDuplicatorVars.restUrl}post-data/${postId}`,
              {
                headers: {
                  'X-WP-Nonce': postDuplicatorVars.nonce,
                },
              }
            )
            
            if (postDataResponse.ok) {
              const postData = await postDataResponse.json()
              taxonomies = postData.taxonomies || []
              customMeta = postData.customMeta || []
            }
          } catch (error) {
            console.error('Error fetching post data:', error)
            // Continue without taxonomy/meta data
          }
          
          setCurrentPost({
            id: post.id,
            title: post.title.rendered,
            type: post.type,
            status: post.status,
            slug: post.slug,
            date: post.date,
            author: authorName,
            authorId: post.author, // Add author ID for settings component
            taxonomies: taxonomies,
            customMeta: customMeta,
          })
          setIsModalOpen(true)
        } catch (error) {
          console.error('Error fetching post:', error)
          showSnackbar(
            'Error loading post data. Please try again.',
            'error'
          )
        }
      }
    }

    document.body.addEventListener('click', handleClick)
    return () => {
      document.body.removeEventListener('click', handleClick)
    }
  }, [])

  return (
    <DuplicateModal
      isOpen={isModalOpen}
      onClose={() => setIsModalOpen(false)}
      onDuplicate={handleDuplicate}
      originalPost={currentPost}
      defaultSettings={postDuplicatorVars.defaultSettings}
      postTypes={postDuplicatorVars.postTypes}
      statusChoices={postDuplicatorVars.statusChoices}
      siteUrl={postDuplicatorVars.siteUrl}
      currentUser={postDuplicatorVars.currentUser}
    />
  )
}

// Mount the React component
document.addEventListener('DOMContentLoaded', function () {
  const modalRoot = document.createElement('div')
  modalRoot.id = 'duplicate-post-modal-root'
  document.body.appendChild(modalRoot)

  // Use createRoot for React 18
  const root = createRoot(modalRoot)
  root.render(<DuplicatePostHandler />)
})
