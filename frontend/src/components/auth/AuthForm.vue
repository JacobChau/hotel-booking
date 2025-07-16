<template>
  <form @submit.prevent="$emit('submit')" class="auth-form" v-bind="$attrs">
    <div class="form-header">
      <h2 class="form-title">{{ title }}</h2>
      <p class="form-subtitle">{{ subtitle }}</p>
    </div>

    <div class="form-content">
      <slot />
    </div>
  </form>
</template>

<script>
export default {
  name: 'AuthForm',
  inheritAttrs: false,
  emits: ['submit'],
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.auth-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
}

.form-header {
  text-align: center;
}

.form-title {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  color: var(--black);
  margin: 0 0 var(--spacing-sm) 0;
}

.form-subtitle {
  color: var(--gray-600);
  margin: 0;
}

.form-content {
  display: flex;
  flex-direction: column;
}
</style> 