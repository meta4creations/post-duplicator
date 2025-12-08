import { useState, useEffect } from '@wordpress/element'
import {
  Modal,
  Button,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
} from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import { edit, check } from '@wordpress/icons'
import DuplicateSettingsFields from './DuplicateSettingsFields'

const DuplicateModal = ({
  isOpen,
  onClose,
  onDuplicate,
  originalPost,
  defaultSettings,
  postTypes,
  statusChoices,
  siteUrl,
  currentUser,
}) => {
  const [settings, setSettings] = useState(defaultSettings)
  const [showCustomize, setShowCustomize] = useState(false)
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    if (isOpen) {
      setSettings(defaultSettings)
      setShowCustomize(false)
    }
  }, [isOpen, defaultSettings])

  const getPreviewTitle = () => {
    if (!originalPost?.title) return ''
    // Use fullTitle if available (when customizing), otherwise use original + suffix
    if (settings.fullTitle) {
      return settings.fullTitle
    }
    return `${originalPost.title} ${settings.title}`
  }

  const getPreviewPostType = () => {
    const targetType =
      settings.type === 'same' ? originalPost.type : settings.type
    return postTypes[targetType] || targetType
  }

  const getPreviewStatus = () => {
    const targetStatus =
      settings.status === 'same' ? originalPost.status : settings.status
    return statusChoices[targetStatus] || targetStatus
  }

  const getPreviewDate = () => {
    let timestamp

    if (settings.customDate) {
      // Use custom date if provided
      timestamp = new Date(settings.customDate).getTime()
    } else if (settings.timestamp === 'duplicate' && originalPost.date) {
      // Use original post date
      timestamp = new Date(originalPost.date).getTime()
    } else {
      // Use current time
      timestamp = Date.now()
    }

    // Apply time offset if enabled
    if (settings.time_offset) {
      const offsetMs =
        (parseInt(settings.time_offset_days || 0) * 86400 +
          parseInt(settings.time_offset_hours || 0) * 3600 +
          parseInt(settings.time_offset_minutes || 0) * 60 +
          parseInt(settings.time_offset_seconds || 0)) *
        1000

      if (settings.time_offset_direction === 'newer') {
        timestamp += offsetMs
      } else {
        timestamp -= offsetMs
      }
    }

    const date = new Date(timestamp)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${year}-${month}-${day} ${hours}:${minutes}`
  }

  const getPreviewAuthor = () => {
    if (settings.post_author === 'current_user') {
      return currentUser?.name || __('Current User', 'post-duplicator')
    } else if (originalPost?.author) {
      return originalPost.author
    }
    return __('Original Author', 'post-duplicator')
  }

  const getPreviewSlug = () => {
    if (!originalPost?.slug) return ''
    // Use fullSlug if available (when customizing), otherwise use original + suffix
    if (settings.fullSlug) {
      return settings.fullSlug
    }
    return `${originalPost.slug}-${settings.slug || 'copy'}`
  }

  const getPreviewUrl = () => {
    const slug = getPreviewSlug()
    if (!slug || !siteUrl) return ''
    return `${siteUrl}/${slug}/`
  }

  const handleDuplicate = async () => {
    setIsLoading(true)
    try {
      await onDuplicate(settings)
    } catch (error) {
      console.error('Error duplicating:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const getDuplicateButtonLabel = () => {
    const postTypeLabel = getPreviewPostType()
    return __(`Duplicate ${postTypeLabel}`, 'post-duplicator')
  }
  if (!isOpen) return null

  // Header actions for the edit icon
  const headerActions = (
    <Button
      icon={edit}
      variant={showCustomize ? 'primary' : 'secondary'}
      label={
        showCustomize
          ? __('Done Customizing', 'post-duplicator')
          : __('Customize Settings', 'post-duplicator')
      }
      onClick={() => setShowCustomize(!showCustomize)}
      size="small"
    />
  )

  return (
    <Modal
      title={getPreviewTitle()}
      onRequestClose={onClose}
      className="duplicate-post-modal"
      size="large"
      style={{ borderRadius: 0 }}
      headerActions={headerActions}
    >
      <div
        className="duplicate-post-modal__content"
        style={{ paddingBottom: '77px' }}
      >
        {/* Preview Section */}
        <div className="duplicate-post-modal__preview">
          <div className="duplicate-post-modal__preview-item">
            <span className="duplicate-post-modal__preview-label">
              {__('Post Type:', 'post-duplicator')}
            </span>
            <span className="duplicate-post-modal__preview-value">
              {getPreviewPostType()}
            </span>
          </div>
          <div className="duplicate-post-modal__preview-item">
            <span className="duplicate-post-modal__preview-label">
              {__('Post Status:', 'post-duplicator')}
            </span>
            <span className="duplicate-post-modal__preview-value">
              {getPreviewStatus()}
            </span>
          </div>
          <div className="duplicate-post-modal__preview-item">
            <span className="duplicate-post-modal__preview-label">
              {__('Post Date:', 'post-duplicator')}
            </span>
            <span className="duplicate-post-modal__preview-value">
              {getPreviewDate()}
            </span>
          </div>
          <div className="duplicate-post-modal__preview-item">
            <span className="duplicate-post-modal__preview-label">
              {__('Post Author:', 'post-duplicator')}
            </span>
            <span className="duplicate-post-modal__preview-value">
              {getPreviewAuthor()}
            </span>
          </div>
          <div className="duplicate-post-modal__preview-item">
            <span className="duplicate-post-modal__preview-label">
              {__('Post URL:', 'post-duplicator')}
            </span>
            <span className="duplicate-post-modal__preview-value duplicate-post-modal__preview-url">
              {getPreviewUrl()}
            </span>
          </div>
        </div>

        {/* Settings Editor (shown when customize is clicked) */}
        {showCustomize && (
          <div className="duplicate-post-modal__settings">
            <DuplicateSettingsFields
              settings={settings}
              onSettingsChange={setSettings}
              postTypes={postTypes}
              statusChoices={statusChoices}
              originalPost={originalPost}
            />
          </div>
        )}
      </div>
      <HStack
        alignment="right"
        className="duplicate-post-modal__footer"
        style={{
          position: 'absolute',
          bottom: '0px',
          left: '0',
          padding: '20px',
          borderTop: '1px solid rgba(0, 0, 0, 0.1)',
          background: '#FFF',
        }}
      >
        <Button variant="tertiary" onClick={onClose} disabled={isLoading}>
          {__('Cancel', 'post-duplicator')}
        </Button>
        <Button
          variant="primary"
          onClick={handleDuplicate}
          disabled={isLoading}
          isBusy={isLoading}
        >
          {isLoading
            ? __('Duplicating...', 'post-duplicator')
            : getDuplicateButtonLabel()}
        </Button>
      </HStack>
    </Modal>
  )
}

export default DuplicateModal
