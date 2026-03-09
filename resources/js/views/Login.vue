<template>
  <div class="login-wrapper">
    <div class="container-fluid vh-100">
      <div class="row h-100">
        <!-- LEFT SIDE IMAGE -->
        <div class="col-lg-7 d-none d-lg-flex login-image-container">
          <div class="login-overlay">
            <h1 class="login-title">
              PLP Community Outreach Management System
            </h1>
            <p class="login-subtitle">
              Connecting universities and communities through impactful outreach programs.
            </p>
          </div>
        </div>
        <!-- RIGHT SIDE LOGIN -->
        <div class=" col-lg-5 col-md-12 d-flex align-items-center justify-content-center">
          <div class="login-box w-100">
            <div class="card shadow-sm">
              <div class="card-body login-card-body">

                <div class="text-center mb-4">
                  <h3 class="fw-bold">Welcome Back</h3>
                  <p class="text-muted small">
                    Sign in to continue
                  </p>
                </div>

                <form @submit.prevent="submit">

                  <div class="input-group mb-3">
                    <input
                      v-model="form.email"
                      type="email"
                      class="form-control"
                      placeholder="Email"
                    >
                    <span class="input-group-text">
                      <i class="bi bi-envelope"></i>
                    </span>
                  </div>

                  <div class="input-group mb-3">
                    <input
                      v-model="form.password"
                      type="password"
                      class="form-control"
                      placeholder="Password"
                    >
                    <span class="input-group-text">
                      <i class="bi bi-lock"></i>
                    </span>
                  </div>
                  <div v-if="error" class="alert alert-danger py-2 small">
                    {{ error }}
                  </div>
                  <button class="btn btn-primary w-100">
                    Login
                  </button>
                </form>
                <p class="text-center text-muted small mt-3">
                  Outreach Program Extension System
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const error = ref(null)

const submit = async () => {
  try {
    error.value = null
    await auth.login(form)
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed.'
  }
}
</script>

<style scoped>

.login-wrapper {
  height: 100vh;
  background: #f4f6f9;
}

/* LEFT IMAGE PANEL */
.login-image-container {
  background-image: url('/images/plp-campus.webp');
  background-size: cover;
  background-position: center;
  position: relative;
}

.login-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 80px;
  color: white;
}

.login-title {
  font-size: 38px;
  font-weight: 700;
}

.login-subtitle {
  max-width: 420px;
  opacity: 0.9;
}

/* LOGIN CARD */
.login-box {
  max-width: 420px;
}

.card {
  border-radius: 10px;
}

/* MOBILE */
@media (max-width: 991px) {

  .login-wrapper {
    background-image: url('/images/plp-campus.webp');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
  }

  .login-wrapper::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
  }

  .login-box {
    position: relative;
    z-index: 2;
    background: transparent;
  }

}

</style>