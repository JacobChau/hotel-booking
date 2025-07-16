<template>
  <div class="search-autocomplete">
    <input
      ref="inputRef"
      type="text"
      v-model="inputValue"
      @input="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
      @keydown="handleKeydown"
      :placeholder="placeholder"
      :class="inputClass"
    />
    
    <div v-if="showSuggestions && suggestions.length > 0" class="suggestions-dropdown">
      <div
        v-for="(suggestion, index) in suggestions"
        :key="suggestion.id"
        :class="['suggestion-item', { active: index === activeIndex }]"
        @mousedown="selectSuggestion(suggestion)"
        @mouseenter="activeIndex = index"
      >
        <div class="suggestion-content">
          <div class="suggestion-name">{{ suggestion.name }}</div>
          <div class="suggestion-description">{{ suggestion.description }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue'
import { bookingsAPI } from '../api/bookings'

export default {
  name: 'SearchAutocomplete',
  emits: ['update:modelValue', 'suggestion-selected'],
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Enter hotel name, city, or destination'
    },
    inputClass: {
      type: String,
      default: 'search-input'
    },
    debounceMs: {
      type: Number,
      default: 300
    },
    minChars: {
      type: Number,
      default: 2
    }
  },
  setup(props, { emit }) {
    const inputRef = ref(null)
    const suggestions = ref([])
    const loading = ref(false)
    const showSuggestions = ref(false)
    const activeIndex = ref(-1)
    const debounceTimer = ref(null)

    const inputValue = computed({
      get: () => props.modelValue,
      set: (value) => emit('update:modelValue', value)
    })

    const handleInput = (event) => {
      const value = event.target.value
      inputValue.value = value
      
      if (debounceTimer.value) {
        clearTimeout(debounceTimer.value)
      }
      
      activeIndex.value = -1
      
      debounceTimer.value = setTimeout(() => {
        if (value.trim().length >= props.minChars) {
          fetchSuggestions(value.trim())
        } else {
          suggestions.value = []
          showSuggestions.value = false
        }
      }, props.debounceMs)
    }

    const handleFocus = () => {
      if (inputValue.value.trim().length >= props.minChars) {
        showSuggestions.value = true
      }
    }

    const handleBlur = () => {
      setTimeout(() => {
        showSuggestions.value = false
        activeIndex.value = -1
      }, 200)
    }

    const handleKeydown = (event) => {
      if (!showSuggestions.value || suggestions.value.length === 0) return

      switch (event.key) {
        case 'ArrowDown':
          event.preventDefault()
          activeIndex.value = Math.min(activeIndex.value + 1, suggestions.value.length - 1)
          break
        case 'ArrowUp':
          event.preventDefault()
          activeIndex.value = Math.max(activeIndex.value - 1, -1)
          break
        case 'Enter':
          event.preventDefault()
          if (activeIndex.value >= 0) {
            selectSuggestion(suggestions.value[activeIndex.value])
          }
          break
        case 'Escape':
          showSuggestions.value = false
          activeIndex.value = -1
          inputRef.value?.blur()
          break
      }
    }

    const fetchSuggestions = async (query) => {
      loading.value = true
      try {
        const response = await bookingsAPI.getRoomSuggestions(query, 8)
        if (response.success) {
          suggestions.value = response.data
          showSuggestions.value = true
        }
      } catch (error) {
        console.error('Error fetching suggestions:', error)
        suggestions.value = []
      } finally {
        loading.value = false
      }
    }

    const selectSuggestion = (suggestion) => {
      inputValue.value = suggestion.name
      showSuggestions.value = false
      activeIndex.value = -1
      emit('suggestion-selected', suggestion)
      
      nextTick(() => {
        inputRef.value?.focus()
      })
    }

    return {
      inputRef,
      inputValue,
      suggestions,
      loading,
      showSuggestions,
      activeIndex,
      handleInput,
      handleFocus,
      handleBlur,
      handleKeydown,
      selectSuggestion
    }
  }
}
</script>

<style scoped>
.search-autocomplete {
  position: relative;
  width: 100%;
}

.search-autocomplete input {
  border: none;
  outline: none;
  font-size: 14px;
  color: #333;
  background: transparent;
  width: 100%;
}

.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e0e0e0;
  border-top: none;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
}

.suggestion-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s ease;
}

.suggestion-item:hover,
.suggestion-item.active {
  background-color: #f8f9fa;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.suggestion-name {
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.suggestion-description {
  font-size: 12px;
  color: #666;
  line-height: 1.3;
}


@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .suggestions-dropdown {
    max-height: 200px;
  }
  
  .suggestion-item {
    padding: 10px 12px;
  }
  
  .suggestion-name {
    font-size: 13px;
  }
  
  .suggestion-description {
    font-size: 11px;
  }
}
</style> 