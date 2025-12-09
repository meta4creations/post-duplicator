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
import TaxonomySection from './TaxonomySection'
import CustomMetaSection from './CustomMetaSection'

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
  const [includeTaxonomies, setIncludeTaxonomies] = useState(true)
  const [includeCustomMeta, setIncludeCustomMeta] = useState(true)
  const [taxonomyData, setTaxonomyData] = useState({})
  const [customMetaData, setCustomMetaData] = useState([])

  useEffect(() => {
    if (isOpen) {
      setSettings(defaultSettings)
      setShowCustomize(false)
      setIncludeTaxonomies(true)
      setIncludeCustomMeta(true)
      
      // Initialize taxonomy data from original post
      if (originalPost?.taxonomies) {
        const initialTaxonomyData = {}
        originalPost.taxonomies.forEach((taxonomy) => {
          initialTaxonomyData[taxonomy.slug] = taxonomy.terms.map((term) => term.id)
        })
        setTaxonomyData(initialTaxonomyData)
      } else {
        setTaxonomyData({})
      }
      
      // Initialize custom meta data from original post
      if (originalPost?.customMeta) {
        setCustomMetaData(
          originalPost.customMeta.map((meta) => ({
            key: meta.key,
            value: meta.value,
            type: meta.type || 'string',
            isSerialized: meta.isSerialized || false,
          }))
        )
      } else {
        setCustomMetaData([])
      }
    }
  }, [isOpen, defaultSettings, originalPost])

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

  const getPreviewTaxonomies = () => {
    if (!includeTaxonomies) {
      return __('None (disabled)', 'post-duplicator')
    }
    
    if (!originalPost?.taxonomies || originalPost.taxonomies.length === 0) {
      return __('None', 'post-duplicator')
    }
    
    // Calculate counts from current taxonomyData state
    const taxonomyCount = Object.keys(taxonomyData).length || originalPost.taxonomies.length
    const totalTermsCount = Object.values(taxonomyData).reduce(
      (sum, termIds) => sum + (Array.isArray(termIds) ? termIds.length : 0),
      0
    ) || originalPost.taxonomies.reduce((sum, tax) => sum + tax.terms.length, 0)
    
    return `${taxonomyCount} ${__('taxonomies', 'post-duplicator')}, ${totalTermsCount} ${__('terms', 'post-duplicator')}`
  }

  const getPreviewCustomMeta = () => {
    if (!includeCustomMeta) {
      return __('None (disabled)', 'post-duplicator')
    }
    
    const fieldCount = customMetaData.length || (originalPost?.customMeta?.length || 0)
    return `${fieldCount} ${__('fields', 'post-duplicator')}`
  }

  const handleDuplicate = async () => {
    setIsLoading(true)
    try {
      const duplicateSettings = {
        ...settings,
        includeTaxonomies,
        includeCustomMeta,
        taxonomyData,
        customMetaData,
      }
      await onDuplicate(duplicateSettings)
    } catch (error) {
      console.error('Error duplicating:', error)
    } finally {
      setIsLoading(false)
    }
  }

  const handleTaxonomyChange = (selectedTerms) => {
    setTaxonomyData(selectedTerms)
  }

  const handleCustomMetaChange = (metaFields) => {
    setCustomMetaData(metaFields)
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
          {originalPost?.taxonomies && originalPost.taxonomies.length > 0 && (
            <div className="duplicate-post-modal__preview-item">
              <span className="duplicate-post-modal__preview-label">
                {__('Taxonomies:', 'post-duplicator')}
              </span>
              <span className="duplicate-post-modal__preview-value">
                {getPreviewTaxonomies()}
              </span>
            </div>
          )}
          {originalPost?.customMeta && originalPost.customMeta.length > 0 && (
            <div className="duplicate-post-modal__preview-item">
              <span className="duplicate-post-modal__preview-label">
                {__('Custom Meta:', 'post-duplicator')}
              </span>
              <span className="duplicate-post-modal__preview-value">
                {getPreviewCustomMeta()}
              </span>
            </div>
          )}
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
            
            {originalPost?.taxonomies && originalPost.taxonomies.length > 0 && (
              <TaxonomySection
                taxonomies={originalPost.taxonomies}
                onChange={handleTaxonomyChange}
                enabled={includeTaxonomies}
                onToggle={setIncludeTaxonomies}
              />
            )}
            
            {originalPost?.customMeta && originalPost.customMeta.length > 0 && (
              <CustomMetaSection
                customMeta={originalPost.customMeta}
                onChange={handleCustomMetaChange}
                enabled={includeCustomMeta}
                onToggle={setIncludeCustomMeta}
              />
            )}
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
