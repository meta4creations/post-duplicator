import { useState, useEffect } from '@wordpress/element'
import {
  ToggleControl,
  TextControl,
  TextareaControl,
  Button,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
} from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import { trash } from '@wordpress/icons'

const CustomMetaSection = ({ customMeta, onChange, enabled, onToggle }) => {
  const [isExpanded, setIsExpanded] = useState(false)
  const [metaFields, setMetaFields] = useState([])

  // Initialize meta fields from customMeta
  useEffect(() => {
    if (customMeta && customMeta.length > 0) {
      setMetaFields(
        customMeta.map((meta) => ({
          key: meta.key,
          value: meta.value,
          type: meta.type || 'string',
          isSerialized: meta.isSerialized || false,
          originalValue: meta.originalValue || meta.value,
          isDeleted: false,
        }))
      )
    } else {
      setMetaFields([])
    }
  }, [customMeta])

  // Calculate field count (excluding deleted)
  const fieldCount = metaFields.filter((field) => !field.isDeleted).length

  const handleFieldChange = (index, field, value) => {
    const newFields = [...metaFields]
    newFields[index] = {
      ...newFields[index],
      [field]: value,
    }
    
    // If value changed and it's array/object type, try to detect type
    if (field === 'value' && (newFields[index].type === 'array' || newFields[index].type === 'object')) {
      try {
        const parsed = JSON.parse(value)
        newFields[index].type = Array.isArray(parsed) ? 'array' : 'object'
      } catch (e) {
        // Invalid JSON, keep current type
      }
    }
    
    setMetaFields(newFields)
    
    // Notify parent of change with properly formatted data
    if (onChange) {
      const formattedFields = newFields
        .filter((f) => !f.isDeleted)
        .map((f) => ({
          key: f.key,
          value: f.value,
          type: f.type,
          isSerialized: f.isSerialized,
        }))
      onChange(formattedFields)
    }
  }

  const handleDeleteField = (index) => {
    const newFields = [...metaFields]
    newFields[index].isDeleted = true
    setMetaFields(newFields)
    
    // Notify parent of change with properly formatted data
    if (onChange) {
      const formattedFields = newFields
        .filter((f) => !f.isDeleted)
        .map((f) => ({
          key: f.key,
          value: f.value,
          type: f.type,
          isSerialized: f.isSerialized,
        }))
      onChange(formattedFields)
    }
  }

  const handleAddField = () => {
    const newField = {
      key: '',
      value: '',
      type: 'string',
      isSerialized: false,
      originalValue: '',
      isDeleted: false,
    }
    const newFields = [...metaFields, newField]
    setMetaFields(newFields)
  }

  const handleSectionClick = (e) => {
    // Don't expand if clicking the toggle
    if (e.target.closest('.components-toggle-control')) {
      return
    }
    setIsExpanded(!isExpanded)
  }

  const handleValueBlur = (index, value) => {
    const field = metaFields[index]
    
    // If it's an array or object type, validate JSON
    if (field.type === 'array' || field.type === 'object') {
      try {
        JSON.parse(value)
        // Valid JSON, keep it
      } catch (error) {
        // Invalid JSON, revert to previous value
        handleFieldChange(index, 'value', field.value)
      }
    }
  }

  if (!customMeta || customMeta.length === 0) {
    return null
  }

  return (
    <div className="duplicate-post-modal__custom-meta-section">
      <div
        className="duplicate-post-modal__section-header"
        onClick={handleSectionClick}
        style={{
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          padding: '12px',
          cursor: 'pointer',
          borderBottom: isExpanded ? '1px solid rgba(0, 0, 0, 0.1)' : 'none',
        }}
      >
        <ToggleControl
          checked={enabled}
          onChange={onToggle}
          __nextHasNoMarginBottom
        />
        <div style={{ flex: 1 }}>
          <span className="duplicate-post-modal__section-label">
            {__('Custom Meta', 'post-duplicator')}
          </span>
          <span className="duplicate-post-modal__section-count">
            {' '}
            ({fieldCount} {__('fields', 'post-duplicator')})
          </span>
        </div>
      </div>

      {isExpanded && (
        <div
          className="duplicate-post-modal__section-content"
          style={{ padding: '16px' }}
        >
          <VStack spacing="16px">
            {metaFields.map(
              (field, index) =>
                !field.isDeleted && (
                  <div
                    key={index}
                    style={{
                      padding: '12px',
                      border: '1px solid rgba(0, 0, 0, 0.1)',
                      borderRadius: '4px',
                    }}
                  >
                    <HStack spacing="8px" alignment="top">
                      <div style={{ flex: 1 }}>
                        <TextControl
                          label={__('Key', 'post-duplicator')}
                          value={field.key}
                          onChange={(value) =>
                            handleFieldChange(index, 'key', value)
                          }
                          __nextHasNoMarginBottom
                          __next40pxDefaultSize
                        />
                      </div>
                      <div style={{ flex: 2 }}>
                        {field.type === 'array' || field.type === 'object' ? (
                          <TextareaControl
                            label={__('Value (JSON)', 'post-duplicator')}
                            value={field.value}
                            onChange={(value) =>
                              handleFieldChange(index, 'value', value)
                            }
                            onBlur={(e) =>
                              handleValueBlur(index, e.target.value)
                            }
                            rows={6}
                            __nextHasNoMarginBottom
                            __next40pxDefaultSize
                          />
                        ) : (
                          <TextareaControl
                            label={__('Value', 'post-duplicator')}
                            value={field.value}
                            onChange={(value) =>
                              handleFieldChange(index, 'value', value)
                            }
                            rows={3}
                            __nextHasNoMarginBottom
                            __next40pxDefaultSize
                          />
                        )}
                      </div>
                      <div>
                        <Button
                          icon={trash}
                          variant="tertiary"
                          onClick={() => handleDeleteField(index)}
                          label={__('Delete field', 'post-duplicator')}
                          style={{ marginTop: '32px' }}
                        />
                      </div>
                    </HStack>
                  </div>
                )
            )}
            <Button
              variant="secondary"
              onClick={handleAddField}
              style={{ alignSelf: 'flex-start' }}
            >
              {__('Add Field', 'post-duplicator')}
            </Button>
          </VStack>
        </div>
      )}
    </div>
  )
}

export default CustomMetaSection

