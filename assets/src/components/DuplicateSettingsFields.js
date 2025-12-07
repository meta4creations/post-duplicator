import { useState, useEffect, useRef } from '@wordpress/element'
import {
  SelectControl,
  TextControl,
  CheckboxControl,
  DateTimePicker,
  Dropdown,
  Button,
  Flex,
  FlexBlock,
  FlexItem,
  __experimentalNumberControl as NumberControl,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
} from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import { calendar } from '@wordpress/icons'

const DuplicateSettingsFields = ({
  settings,
  onSettingsChange,
  postTypes,
  statusChoices,
  originalPost,
}) => {
  const [localSettings, setLocalSettings] = useState(settings)
  const [slugManuallyEdited, setSlugManuallyEdited] = useState(false)
  const [fullTitle, setFullTitle] = useState('')
  const [fullSlug, setFullSlug] = useState('')
  const [initialized, setInitialized] = useState(false)

  // Get users list and current user from localized data
  const usersList = window.postDuplicatorVars?.users || []
  const currentUser = window.postDuplicatorVars?.currentUser

  // Initialize selectedAuthorId with a valid default (current user ID or first user in list)
  const defaultAuthorId = currentUser?.id
    ? String(currentUser.id)
    : usersList.length > 0
    ? usersList[0].value
    : ''
  const [selectedAuthorId, setSelectedAuthorId] = useState(defaultAuthorId)
  const [postDate, setPostDate] = useState(new Date().toISOString())
  const [dateInputValue, setDateInputValue] = useState('')

  // Sanitize slug using WordPress-style sanitization
  const sanitizeSlug = (text) => {
    return text
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/[^\w\-]/g, '') // Remove non-word chars except hyphens
      .replace(/\-\-+/g, '-') // Replace multiple hyphens with single hyphen
      .replace(/^-+/, '') // Trim hyphens from start
      .replace(/-+$/, '') // Trim hyphens from end
  }

  // Generate slug from title
  const generateSlugFromTitle = (title) => {
    return sanitizeSlug(title)
  }

  const handleChange = (field, value) => {
    const newSettings = { ...localSettings, [field]: value }

    // If changing timestamp to not-custom, clear customDate
    if (field === 'timestamp' && value !== 'custom') {
      newSettings.customDate = null
    }

    setLocalSettings(newSettings)
    onSettingsChange(newSettings)
  }

  // Initialize full title and slug ONLY on mount or when originalPost changes
  useEffect(() => {
    if (!initialized && originalPost?.title && originalPost?.slug) {
      // Set initial full title
      const initialTitle =
        settings.fullTitle || `${originalPost.title} ${settings.title || ''}`
      setFullTitle(initialTitle.trim())

      // Set initial full slug
      const initialSlug =
        settings.fullSlug || `${originalPost.slug}-${settings.slug || 'copy'}`
      setFullSlug(initialSlug)

      // Set initial author based on default settings
      const initialAuthorId =
        settings.post_author === 'current_user'
          ? String(currentUser?.id || '')
          : String(originalPost.authorId || currentUser?.id || '')
      setSelectedAuthorId(initialAuthorId)

      // Set initial date
      const initialDate = settings.customDate || new Date().toISOString()
      setPostDate(initialDate)
      setDateInputValue(formatDateDisplay(initialDate))

      setInitialized(true)
    }
  }, [originalPost, initialized, settings, currentUser])

  // Update localSettings when settings prop changes (but not title/slug)
  useEffect(() => {
    setLocalSettings(settings)
  }, [settings])

  // Format date for display
  const formatDateDisplay = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  // Handle manual date input and format on blur
  const handleDateBlur = () => {
    try {
      const parsedDate = new Date(dateInputValue)
      if (!isNaN(parsedDate.getTime())) {
        // Valid date - format and save
        const formattedDate = parsedDate.toISOString()
        setPostDate(formattedDate)
        setDateInputValue(formatDateDisplay(formattedDate))
        handleChange('customDate', formattedDate)
      } else {
        // Invalid date - revert to previous valid date display
        setDateInputValue(formatDateDisplay(postDate))
      }
    } catch (error) {
      // Error parsing - revert to previous valid date display
      setDateInputValue(formatDateDisplay(postDate))
    }
  }

  // Handle date input changes (just update display value)
  const handleDateInputChange = (value) => {
    setDateInputValue(value)
  }

  // Handle date picker changes
  const handleDatePickerChange = (value) => {
    setPostDate(value)
    setDateInputValue(formatDateDisplay(value))
    handleChange('customDate', value)
  }

  const handleTitleChange = (value) => {
    setFullTitle(value)

    // Auto-generate slug from title if slug hasn't been manually edited
    if (!slugManuallyEdited && originalPost?.title) {
      const newSlug = generateSlugFromTitle(value)
      setFullSlug(newSlug)
    }

    // Pass full title to parent
    handleChange('fullTitle', value)
  }

  const handleSlugChange = (value) => {
    setFullSlug(value)
    setSlugManuallyEdited(true)

    // Pass full slug to parent
    handleChange('fullSlug', value)
  }

  const handleSlugBlur = () => {
    // If slug is empty, regenerate from title
    if (!fullSlug || fullSlug.trim() === '') {
      const regeneratedSlug = generateSlugFromTitle(fullTitle)
      setFullSlug(regeneratedSlug)
      handleChange('fullSlug', regeneratedSlug)
      // Reset manual edit flag since we're regenerating
      setSlugManuallyEdited(false)
    } else {
      // Sanitize slug on blur
      const sanitized = sanitizeSlug(fullSlug)
      setFullSlug(sanitized)
      handleChange('fullSlug', sanitized)
    }
  }

  const formatChoices = (choices) => {
    if (Array.isArray(choices)) {
      return choices
    }
    return Object.entries(choices).map(([value, label]) => ({
      value,
      label,
    }))
  }

  return (
    <VStack className="duplicate-settings-fields" spacing="20px">
      <HStack spacing="16px" alignment="stretch">
        <TextControl
          label={__('Title', 'post-duplicator')}
          value={fullTitle}
          onChange={handleTitleChange}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
        <TextControl
          label={__('Slug', 'post-duplicator')}
          value={fullSlug}
          onChange={handleSlugChange}
          onBlur={handleSlugBlur}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </HStack>
      <HStack spacing="16px" alignment="stretch">
        <SelectControl
          label={__('Post Status', 'post-duplicator')}
          value={localSettings.status}
          options={formatChoices(statusChoices)}
          onChange={(value) => handleChange('status', value)}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
        <SelectControl
          label={__('Post Type', 'post-duplicator')}
          value={localSettings.type}
          options={formatChoices(postTypes)}
          onChange={(value) => handleChange('type', value)}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </HStack>
      <HStack spacing="16px" alignment="stretch">
        <SelectControl
          label={__('Post Author', 'post-duplicator')}
          value={selectedAuthorId || ''}
          options={usersList}
          onChange={(value) => {
            setSelectedAuthorId(value)
            handleChange('selectedAuthorId', parseInt(value))
          }}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
        <div style={{ flex: 1, position: 'relative' }}>
          <TextControl
            label={__('Post Date', 'post-duplicator')}
            value={dateInputValue}
            onChange={handleDateInputChange}
            onBlur={handleDateBlur}
            __nextHasNoMarginBottom
            __next40pxDefaultSize
          />
          <Dropdown
            position="bottom right"
            popoverProps={{
              placement: 'bottom-end',
              offset: 8,
            }}
            renderToggle={({ isOpen, onToggle }) => (
              <Button
                onClick={onToggle}
                aria-expanded={isOpen}
                icon={calendar}
                style={{
                  position: 'absolute',
                  right: '2px',
                  top: '26px',
                  minWidth: '36px',
                  height: '36px',
                  padding: '0',
                }}
                variant="tertiary"
              />
            )}
            renderContent={({ onClose }) => (
              <div style={{ padding: '16px' }}>
                <DateTimePicker
                  currentDate={postDate}
                  onChange={handleDatePickerChange}
                  is12Hour={false}
                />
              </div>
            )}
          />
        </div>
      </HStack>
    </VStack>
  )
}

export default DuplicateSettingsFields
