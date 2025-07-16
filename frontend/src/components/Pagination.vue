<template>
  <div class="pagination" v-if="paginationData.last_page > 1">
    <div class="pagination-info">
      Showing {{ paginationData.from }} to {{ paginationData.to }} of {{ paginationData.total }} results
    </div>
    <div class="pagination-controls">
      <button 
        @click="$emit('page-change', paginationData.current_page - 1)"
        :disabled="paginationData.current_page === 1 || loading"
        class="pagination-btn pagination-btn--prev"
      >
        ← Previous
      </button>
      
      <div class="pagination-numbers">
        <button
          v-for="page in getVisiblePages()"
          :key="page"
          @click="$emit('page-change', page)"
          :disabled="loading"
          :class="[
            'pagination-btn',
            'pagination-btn--number',
            { 'pagination-btn--active': page === paginationData.current_page }
          ]"
        >
          {{ page }}
        </button>
      </div>
      
      <button 
        @click="$emit('page-change', paginationData.current_page + 1)"
        :disabled="paginationData.current_page === paginationData.last_page || loading"
        class="pagination-btn pagination-btn--next"
      >
        Next →
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
  props: {
    paginationData: {
      type: Object,
      required: true,
      default: () => ({
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      })
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['page-change'],
  methods: {
    getVisiblePages() {
      const current = this.paginationData.current_page
      const last = this.paginationData.last_page
      const pages = []
      
      const maxVisible = 7
      let start = Math.max(1, current - Math.floor(maxVisible / 2))
      let end = Math.min(last, start + maxVisible - 1)
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    }
  }
}
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 40px;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.pagination-info {
  font-size: 14px;
  color: #666;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-numbers {
  display: flex;
  gap: 4px;
}

.pagination-btn {
  background: white;
  color: #005999;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.pagination-btn:hover:not(:disabled) {
  background: #f0f8ff;
  border-color: #005999;
}

.pagination-btn:disabled {
  background: #f5f5f5;
  color: #ccc;
  cursor: not-allowed;
  border-color: #e0e0e0;
}

.pagination-btn--prev,
.pagination-btn--next {
  background: #005999;
  color: white;
  border-color: #005999;
}

.pagination-btn--prev:hover:not(:disabled),
.pagination-btn--next:hover:not(:disabled) {
  background: #005999;
}

.pagination-btn--active {
  background: #005999;
  color: white;
  border-color: #005999;
}

.pagination-btn--active:hover {
  background: rgba(0, 53, 128, 1);
}

/* Responsive Design */
@media (max-width: 768px) {
  .pagination {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
    flex-wrap: wrap;
  }

  .pagination-numbers {
    order: 2;
  }

  .pagination-btn--prev {
    order: 1;
  }

  .pagination-btn--next {
    order: 3;
  }
}
</style> 