<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import MainLayout from "@/components/layout/MainLayout.vue";
import { useApiErrors } from "@/composables/useApiErrors";
import profileService from "@/services/profileService";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const loading = ref(false);
const savingProfile = ref(false);
const savingPassword = ref(false);
const profileNotice = ref("");
const passwordNotice = ref("");

const {
    errors: profileErrors,
    generalError: profileGeneralError,
    clearErrors: clearProfileErrors,
    captureError: captureProfileError,
    firstError: firstProfileError,
} = useApiErrors();

const {
    errors: passwordErrors,
    generalError: passwordGeneralError,
    clearErrors: clearPasswordErrors,
    captureError: capturePasswordError,
    firstError: firstPasswordError,
} = useApiErrors();

const profileForm = reactive({
    first_name: "",
    middle_name: "",
    last_name: "",
    birthday: "",
    contact_number: "",
    email: "",
});

const passwordForm = reactive({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const roleDisplay = computed(() =>
    authStore.user?.role?.display_name ||
    authStore.user?.role_name?.replaceAll("_", " ") ||
    "Not assigned",
);
const collegeDisplay = computed(() =>
    authStore.user?.college?.name ||
    authStore.user?.college_name ||
    "Not assigned",
);

function populateProfile(user) {
    Object.assign(profileForm, {
        first_name: user.first_name || "",
        middle_name: user.middle_name || "",
        last_name: user.last_name || "",
        birthday: user.birthday || "",
        contact_number: user.contact_number || "",
        email: user.email || "",
    });
}

async function loadProfile() {
    loading.value = true;
    clearProfileErrors();

    try {
        const response = await profileService.get();
        authStore.setUser(response.data);
        populateProfile(response.data);
    } catch (error) {
        captureProfileError(error);
    } finally {
        loading.value = false;
    }
}

async function updateProfile() {
    savingProfile.value = true;
    profileNotice.value = "";
    clearProfileErrors();

    try {
        const response = await profileService.update({
            ...profileForm,
            middle_name: profileForm.middle_name || null,
            birthday: profileForm.birthday || null,
            contact_number: profileForm.contact_number || null,
        });

        authStore.setUser(response.data);
        populateProfile(response.data);
        profileNotice.value = response.message;
    } catch (error) {
        captureProfileError(error);
    } finally {
        savingProfile.value = false;
    }
}

function resetPasswordForm() {
    Object.assign(passwordForm, {
        current_password: "",
        password: "",
        password_confirmation: "",
    });
}

async function updatePassword() {
    savingPassword.value = true;
    passwordNotice.value = "";
    clearPasswordErrors();

    try {
        const response = await profileService.updatePassword(passwordForm);
        resetPasswordForm();
        passwordNotice.value = response.message;
    } catch (error) {
        capturePasswordError(error);
    } finally {
        savingPassword.value = false;
    }
}

onMounted(loadProfile);
</script>

<template>
  <MainLayout>
    <section class="content">
      <div class="container-fluid py-3">
        <div class="mb-3">
          <h1 class="h3 mb-1">My Profile</h1>
          <p class="text-muted mb-0">Manage your personal information and account password.</p>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div v-else class="row g-3">
          <div class="col-lg-8">
            <form class="card h-100" @submit.prevent="updateProfile">
              <div class="card-header"><h2 class="card-title mb-0">Personal Information</h2></div>
              <div class="card-body">
                <div v-if="profileNotice" class="alert alert-success">{{ profileNotice }}</div>
                <div v-if="profileGeneralError" class="alert alert-danger">{{ profileGeneralError }}</div>

                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input v-model.trim="profileForm.first_name" class="form-control" :class="{ 'is-invalid': firstProfileError('first_name') }" maxlength="255">
                    <div class="invalid-feedback">{{ firstProfileError('first_name') }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Middle Name</label>
                    <input v-model.trim="profileForm.middle_name" class="form-control" :class="{ 'is-invalid': firstProfileError('middle_name') }" maxlength="255">
                    <div class="invalid-feedback">{{ firstProfileError('middle_name') }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input v-model.trim="profileForm.last_name" class="form-control" :class="{ 'is-invalid': firstProfileError('last_name') }" maxlength="255">
                    <div class="invalid-feedback">{{ firstProfileError('last_name') }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input v-model.trim="profileForm.email" type="email" class="form-control" :class="{ 'is-invalid': firstProfileError('email') }" maxlength="255">
                    <div class="invalid-feedback">{{ firstProfileError('email') }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <input v-model.trim="profileForm.contact_number" class="form-control" :class="{ 'is-invalid': firstProfileError('contact_number') }" maxlength="20">
                    <div class="invalid-feedback">{{ firstProfileError('contact_number') }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Birthday</label>
                    <input v-model="profileForm.birthday" type="date" class="form-control" :max="new Date().toISOString().slice(0, 10)" :class="{ 'is-invalid': firstProfileError('birthday') }">
                    <div class="invalid-feedback">{{ firstProfileError('birthday') }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <input :value="roleDisplay" class="form-control text-capitalize" disabled>
                    <small class="text-muted">Role assignments are managed by an administrator.</small>
                  </div>
                  <div class="col-12">
                    <label class="form-label">College</label>
                    <input :value="collegeDisplay" class="form-control" disabled>
                    <small class="text-muted">College assignments are managed by an administrator.</small>
                  </div>
                </div>
              </div>
              <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit" :disabled="savingProfile">
                  <span v-if="savingProfile" class="spinner-border spinner-border-sm me-1"></span>
                  {{ savingProfile ? "Saving..." : "Save Profile" }}
                </button>
              </div>
            </form>
          </div>

          <div class="col-lg-4">
            <form class="card" @submit.prevent="updatePassword">
              <div class="card-header"><h2 class="card-title mb-0">Change Password</h2></div>
              <div class="card-body">
                <div v-if="passwordNotice" class="alert alert-success">{{ passwordNotice }}</div>
                <div v-if="passwordGeneralError" class="alert alert-danger">{{ passwordGeneralError }}</div>

                <div class="mb-3">
                  <label class="form-label">Current Password</label>
                  <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" class="form-control" :class="{ 'is-invalid': firstPasswordError('current_password') }">
                  <div class="invalid-feedback">{{ firstPasswordError('current_password') }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input v-model="passwordForm.password" type="password" autocomplete="new-password" class="form-control" :class="{ 'is-invalid': firstPasswordError('password') }">
                  <div class="invalid-feedback">{{ firstPasswordError('password') }}</div>
                  <small class="text-muted">Use at least eight characters.</small>
                </div>
                <div>
                  <label class="form-label">Confirm New Password</label>
                  <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" class="form-control">
                </div>
              </div>
              <div class="card-footer text-end">
                <button class="btn btn-outline-primary" type="submit" :disabled="savingPassword">
                  <span v-if="savingPassword" class="spinner-border spinner-border-sm me-1"></span>
                  {{ savingPassword ? "Updating..." : "Update Password" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>
