<template>

    <MainLayout>

        <CrudPage
            title="Users"
            add-label="Add User"
            @create="openCreate"
        >

            <CrudTable
                :columns="columns"
                :rows="users.data || []"
                @edit="openEdit"
                @delete="remove"
            />

            <CrudPagination
                :current-page="users.current_page || 1"
                :last-page="users.last_page || 1"
                :prev="!!users.prev_page_url"
                :next="!!users.next_page_url"
                @change="fetch"
            />

        </CrudPage>

        <CrudModal
            ref="modalRef"
            :title="isEdit ? 'Edit User' : 'Create User'"
            :fields="fields"
            :form="form"
            :errors="errors"
            @submit="submit"
        />

    </MainLayout>

</template>

<script setup>

import { ref, reactive, onMounted } from 'vue'
import MainLayout from '../components/layout/MainLayout.vue'
import CrudPage from '../components/crud/CrudPage.vue'
import CrudTable from '../components/crud/CrudTable.vue'
import CrudPagination from '../components/crud/CrudPagination.vue'
import CrudModal from '../components/crud/CrudModal.vue'
import userService from '../services/userService'
// import roleService from '../services/roleService'
import collegeService from '../services/collegeService'
import api from '../services/api'
import { computed } from 'vue'

// DATA TO BE USED IN TABLE COMPONENT AND MODAL COMPONENT
const users = ref({
    data: []
})
const roles = ref([])
const colleges = ref([])
// MODAL RELATED VARIABLES
const modalRef = ref()
const isEdit = ref(false)
const editingId = ref(null)

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = reactive({
    first_name: '',
    middle_name: '',
    last_name: '',
    birthday: '',
    contact_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
    college_id: ''
})

const errors = reactive({})

/*
|--------------------------------------------------------------------------
| TABLE COLUMNS
|--------------------------------------------------------------------------
*/

const columns = [

    {
        key: 'full_name',
        label: 'Full Name'
    },

    {
        key: 'email',
        label: 'Email'
    },

    {
        key: 'contact_number',
        label: 'Contact No.'
    },

    {
        key: 'role_name',
        label: 'Role'
    },

    {
        key: 'college_name',
        label: 'College'
    }

]

/*
|--------------------------------------------------------------------------
| FORM FIELDS
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/

const fetch = async (page = 1) => {

    try {

        const response = await userService.get(page)

        users.value = response.data

    } catch (error) {

        console.error(error)

    }

}

/*
|--------------------------------------------------------------------------
| LOAD DROPDOWNS
|--------------------------------------------------------------------------
*/

const fetchRoles = async () => {

  const response = await api.get('/roles')
  roles.value = response.data
//   alert(JSON.stringify(roles.value))
}

const fields = computed(() => [
     {
            key: 'first_name',
            label: 'First Name',
            type: 'text'
        },
        {
            key: 'middle_name',
            label: 'Middle Name',
            type: 'text'
        },
        {
            key: 'last_name',
            label: 'Last Name',
            type: 'text'
        },
        {
            key: 'birthday',
            label: 'Birthday',
            type: 'date'
        },
        {
            key: 'contact_number',
            label: 'Contact Number',
            type: 'text'
        },
        {
            key: 'email',
            label: 'Email',
            type: 'email'
        },
        {
            key: 'password',
            label: 'Password',
            type: 'password',
            required: !isEdit.value,
            toggleVisibility: true
        },
        {
            key: 'password_confirmation',
            label: isEdit.value
                ? 'Confirm Password'
                : 'Confirm Password',
            type: 'password'
        },
        {
            key: 'role_id',
            label: 'Role',
            type: 'select',
            options: roles.value.map(role => ({
                value: role.id,
                label: role.name
            }))
        },

        {
            key: 'college_id',
            label: 'College',
            type: 'select',
            options: colleges.value.map(college => ({
                value: college.id,
                label: college.name
            }))
        }
])

const loadDependencies = async () => {

    const [
        collegesResponse
    ] = await Promise.all([
        // roleService.all(),
        collegeService.all()
    ])

    // roles.value = rolesResponse.data
    colleges.value = collegesResponse.data
}

/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

const openCreate = () => {

    resetForm()

    isEdit.value = false

    modalRef.value.open()

}

/*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

const openEdit = (user) => {

    resetForm()

    isEdit.value = true

    editingId.value = user.id

    Object.assign(form, {

        first_name: user.first_name,
        middle_name: user.middle_name,
        last_name: user.last_name,
        birthday: user.birthday,
        contact_number: user.contact_number,
        email: user.email,
        role_id: user.role_id,
        college_id: user.college_id,
        password: '',
        password_confirmation: '',

    })

    modalRef.value.open()

}

/*
|--------------------------------------------------------------------------
| SAVE
|--------------------------------------------------------------------------
*/

const submit = async () => {

    clearErrors()

    try {

        if (isEdit.value) {

            await userService.update(
                editingId.value,
                form
            )

        } else {

            await userService.store(form)

        }

        modalRef.value.close()

        fetch()

    } catch (error) {

        if (error.response?.data?.errors) {

            Object.assign(
                errors,
                error.response.data.errors
            )

        }

    }

}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

const remove = async (user) => {

    if (
        !confirm(
            `Delete ${user.full_name}?`
        )
    ) {
        return
    }

    await userService.delete(user.id)

    fetch()

}

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const clearErrors = () => {

    Object.keys(errors)
        .forEach(
            key => delete errors[key]
        )

}

const resetForm = () => {

    Object.assign(form, {

        first_name: '',
        middle_name: '',
        last_name: '',
        birthday: '',
        contact_number: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: '',
        college_id: ''

    })

    editingId.value = null

    clearErrors()

}

/*
|--------------------------------------------------------------------------
| INIT
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    await loadDependencies()
    await fetch()
    await fetchRoles();

})

</script>
