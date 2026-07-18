<template>
    <MainLayout>
        <CrudPage
            title="Outreach Programs"
            add-label="Add Program"
            @create="openCreate"
        >
            <CrudTable
                :columns="columns"
                :rows="formattedPrograms"
                @edit="openEdit"
                @delete="remove"
            />

            <CrudPagination
                :current-page="programs.current_page || 1"
                :last-page="programs.last_page || 1"
                :prev="!!programs.prev_page_url"
                :next="!!programs.next_page_url"
                @change="fetch"
            />
        </CrudPage>

        <CrudModal
            ref="modalRef"
            :title="isEdit ? 'Edit Outreach Program' : 'Create Outreach Program'"
            :fields="fields"
            :form="form"
            :errors="errors"
            :submit-label="isEdit ? 'Update' : 'Save'"
            @submit="submit"
        />
    </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

import MainLayout from '../components/layout/MainLayout.vue'
import CrudPage from '../components/crud/CrudPage.vue'
import CrudTable from '../components/crud/CrudTable.vue'
import CrudPagination from '../components/crud/CrudPagination.vue'
import CrudModal from '../components/crud/CrudModal.vue'

import outreachProgramService from '../services/outreachProgramService'

const programs = ref({ data: [] })

const modalRef = ref(null)
const isEdit = ref(false)
const editingId = ref(null)

const form = reactive({
    name: '',
    category: '',
    description: '',
    typical_budget: '',
    typical_duration_days: '',
    is_active: true
})

const errors = reactive({})

const columns = [
    { key: 'name', label: 'Program Name' },
    { key: 'category', label: 'Category' },
    { key: 'budget_display', label: 'Typical Budget' },
    { key: 'duration_display', label: 'Duration' },
    { key: 'status_display', label: 'Status' }
]

const fields = computed(() => [
    {
        key: 'name',
        label: 'Program Name',
        type: 'text',
        required: true,
        col: 'col-md-6 col-12'
    },
    {
        key: 'category',
        label: 'Category',
        type: 'text',
        required: true,
        col: 'col-md-6 col-12'
    },
    {
        key: 'description',
        label: 'Description',
        type: 'textarea',
        rows: 4,
        col: 'col-12'
    },
    {
        key: 'typical_budget',
        label: 'Typical Budget',
        type: 'number',
        placeholder: 'Example: 25000',
        col: 'col-md-6 col-12'
    },
    {
        key: 'typical_duration_days',
        label: 'Typical Duration Days',
        type: 'number',
        placeholder: 'Example: 3',
        col: 'col-md-6 col-12'
    },
    {
        key: 'is_active',
        label: 'Active',
        type: 'checkbox',
        checkboxLabel: 'Program is active',
        col: 'col-12'
    }
])

const formattedPrograms = computed(() => {
    return (programs.value.data || []).map(program => ({
        ...program,
        budget_display: formatMoney(program.typical_budget),
        duration_display: formatDuration(program.typical_duration_days),
        status_display: program.is_active ? 'Active' : 'Inactive'
    }))
})

const fetch = async (page = 1) => {
    try {
        const response = await outreachProgramService.get(page)
        programs.value = response.data
    } catch (error) {
        console.error('Failed to fetch outreach programs:', error)
    }
}

const openCreate = () => {
    resetForm()
    isEdit.value = false
    modalRef.value.open()
}

const openEdit = (program) => {
    resetForm()

    isEdit.value = true
    editingId.value = program.id

    Object.assign(form, {
        name: program.name || '',
        category: program.category || '',
        description: program.description || '',
        typical_budget: program.typical_budget || '',
        typical_duration_days: program.typical_duration_days || '',
        is_active: Boolean(program.is_active)
    })

    modalRef.value.open()
}

const submit = async () => {
    clearErrors()

    try {
        const payload = {
            ...form,
            is_active: Boolean(form.is_active)
        }

        if (isEdit.value) {
            await outreachProgramService.update(editingId.value, payload)
        } else {
            await outreachProgramService.store(payload)
        }

        modalRef.value.close()
        await fetch(programs.value.current_page || 1)

    } catch (error) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors)
            return
        }

        console.error('Failed to save outreach program:', error)
    }
}

const remove = async (program) => {
    if (!confirm(`Delete ${program.name}?`)) return

    try {
        await outreachProgramService.delete(program.id)
        await fetch(programs.value.current_page || 1)
    } catch (error) {
        console.error('Failed to delete outreach program:', error)
    }
}

const resetForm = () => {
    Object.assign(form, {
        name: '',
        category: '',
        description: '',
        typical_budget: '',
        typical_duration_days: '',
        is_active: true
    })

    editingId.value = null
    clearErrors()
}

const clearErrors = () => {
    Object.keys(errors).forEach(key => delete errors[key])
}

const formatMoney = (value) => {
    if (value === null || value === undefined || value === '') {
        return '-'
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value)
}

const formatDuration = (days) => {
    if (!days) return '-'

    return Number(days) === 1
        ? '1 day'
        : `${days} days`
}

onMounted(fetch)
</script>
