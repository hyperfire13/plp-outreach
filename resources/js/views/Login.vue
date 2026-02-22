<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card">
        <div class="card-body login-card-body">
          <h4 class="text-center mb-3">Login</h4>

          <form @submit.prevent="submit">
            <div class="mb-3">
              <input v-model="form.email" type="email" class="form-control" placeholder="Email">
            </div>
            <div class="mb-3">
              <input v-model="form.password" type="password" class="form-control" placeholder="Password">
            </div>
            <button class="btn btn-primary w-100">Login</button>
          </form>

          <p v-if="error" class="text-danger mt-2">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import authService from '../services/authService'

const router = useRouter()
const auth = useAuthStore()

const error = ref(null)

const form = reactive({
  email: '',
  password: ''
})

const submit = async () => {
  try {
    const response = await authService.login(form)
    auth.setUser(response.data.user)
    router.push('/')
  } catch (err) {
    error.value = 'Invalid credentials'
  }
}
</script>