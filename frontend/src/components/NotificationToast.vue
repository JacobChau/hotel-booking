<template>
  <div v-if="notification.show" class="notification-toast" :class="notification.type">
    <div class="notification-content">
      <div class="notification-icon">
        <i v-if="notification.type === 'success'" class="pi pi-check-circle"></i>
        <i v-if="notification.type === 'error'" class="pi pi-times-circle"></i>
        <i v-if="notification.type === 'info'" class="pi pi-info-circle"></i>
      </div>
      <div class="notification-message">{{ notification.message }}</div>
      <button @click="hideNotification" class="notification-close">
        <i class="pi pi-times"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotificationToast',
  props: {
    notification: {
      type: Object,
      required: true,
      default: () => ({
        show: false,
        message: '',
        type: 'success'
      })
    }
  },
  emits: ['hide'],
  methods: {
    hideNotification() {
      this.$emit('hide')
    }
  }
}
</script>

<style scoped>
.notification-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  min-width: 300px;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease-out;
}

.notification-toast.success {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.notification-toast.error {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
}

.notification-toast.info {
  background: #d1ecf1;
  border: 1px solid #bee5eb;
  color: #0c5460;
}

.notification-content {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  gap: 12px;
}

.notification-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.notification-message {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
}

.notification-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.notification-close:hover {
  opacity: 1;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 480px) {
  .notification-toast {
    left: 20px;
    right: 20px;
    min-width: auto;
  }
}
</style> 