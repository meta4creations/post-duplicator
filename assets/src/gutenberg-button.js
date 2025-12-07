import { registerPlugin } from '@wordpress/plugins'
import { PluginPostStatusInfo } from '@wordpress/edit-post'
import { Button } from '@wordpress/components'
import { useSelect } from '@wordpress/data'
import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import DuplicateModal from './components/DuplicateModal'
import './css/gutenberg-button.scss'

const DuplicatePostButton = () => {
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const { postId, postType, postStatus, postTypeLabel, postTitle, postSlug, postDate, postAuthor } = useSelect(
    (select) => {
      const editor = select('core/editor')
      const currentPost = editor.getCurrentPost()
      const currentPostType = editor.getCurrentPostType()
      const postTypeObj = select('core').getPostType(currentPostType)
      const authorId = editor.getEditedPostAttribute('author')
      
      // Get author name
      const author = select('core').getUser(authorId)
      const authorName = author ? author.name : 'Unknown Author'

      return {
        postId: currentPost.id,
        postType: currentPostType,
        postStatus: editor.getEditedPostAttribute('status'),
        postTypeLabel: postTypeObj ? postTypeObj.labels.singular_name : 'Post',
        postTitle: editor.getEditedPostAttribute('title'),
        postSlug: editor.getEditedPostAttribute('slug'),
        postDate: editor.getEditedPostAttribute('date'),
        postAuthor: authorName,
      }
    },
    []
  )

  // Only show for published posts
  if (postStatus !== 'publish' || !postId) {
    return null
  }

  const handleDuplicate = async (settings) => {
    setIsLoading(true)
    setError(null)

    try {
      const response = await fetch(
        `${postDuplicatorVars.restUrl}duplicate-post`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': postDuplicatorVars.nonce,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            original_id: postId,
            ...settings,
          }),
        }
      )

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to duplicate post')
      }

      const data = await response.json()

      // Open the duplicated post in a new tab
      if (data.duplicate_id) {
        const editUrl = `${postDuplicatorVars.siteUrl}/wp-admin/post.php?post=${data.duplicate_id}&action=edit`
        window.open(editUrl, '_blank')
        setIsLoading(false)
        setIsModalOpen(false)
      }
    } catch (err) {
      console.error('Error duplicating post:', err)
      setError(err.message)
      setIsLoading(false)
    }
  }

  const originalPost = {
    id: postId,
    title: postTitle,
    type: postType,
    status: postStatus,
    slug: postSlug,
    date: postDate,
    author: postAuthor,
  }

  return (
    <>
      <PluginPostStatusInfo className="m4c-duplicate-post-status-info">
        <div
          className="m4c-duplicate-post-wrapper"
          style={{ paddingTop: '16px' }}
        >
          <Button
            variant="secondary"
            className="m4c-duplicate-post-gutenberg"
            onClick={() => setIsModalOpen(true)}
            disabled={isLoading}
          >
            {__(`Duplicate ${postTypeLabel}`, 'post-duplicator')}
          </Button>
          {error && <div className="m4c-duplicate-error">{error}</div>}
        </div>
      </PluginPostStatusInfo>
      <DuplicateModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onDuplicate={handleDuplicate}
        originalPost={originalPost}
        defaultSettings={postDuplicatorVars.defaultSettings}
        postTypes={postDuplicatorVars.postTypes}
        statusChoices={postDuplicatorVars.statusChoices}
        siteUrl={postDuplicatorVars.siteUrl}
        currentUser={postDuplicatorVars.currentUser}
      />
    </>
  )
}

registerPlugin('post-duplicator-button', {
  render: DuplicatePostButton,
})
