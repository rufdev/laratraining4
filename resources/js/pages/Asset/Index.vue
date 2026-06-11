<script setup lang="ts">
import ReusableDropDownAction from '@/components/entitycomponents/ReusableDropDownAction.vue'; // Dropdown for row actions (edit/delete)
import ReusableTable from '@/components/entitycomponents/ReusableTable.vue'; // Table component for displaying data
import ReusableAlertDialog from '@/components/entitycomponents/ReusableAlertDialog.vue';
import { AutoForm } from '@/components/ui/auto-form'; // AutoForm component for form handling
import { Button } from '@/components/ui/button'; // Button component
import { Checkbox } from '@/components/ui/checkbox'; // Checkbox component for row selection
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'; // Dialog components for forms
import AppLayout from '@/layouts/AppLayout.vue'; // Layout component for the page
import { Head } from '@inertiajs/vue3'; // Head component for setting the page title

/* Import Utilities */
import { toTypedSchema } from '@vee-validate/zod'; // Utility for converting Zod schemas to Vee-Validate schemas
import axios from 'axios'; // HTTP client for API requests
import { ArrowUpDown, Plus } from 'lucide-vue-next'; // Icons for UI
import { useForm } from 'vee-validate'; // Form validation library
import { h, ref } from 'vue'; // Vue composition API utilities
import { toast } from 'vue-sonner'; // Toast notifications
import * as z from 'zod'; // Zod library for schema validation

/* Import Table Utilities */
import type { ColumnDef } from '@tanstack/vue-table'; // Type definitions for table columns

/* Import Types */
import { BreadcrumbItem } from '@/types'; // Type definition for breadcrumbs
import { parseDate } from '@internationalized/date';

/* Base Entity Configuration */
const baseentityurl = '/assets'; // API endpoint for the entity
const baseentityname = 'Asset'; // Name of the entity

/* Breadcrumbs */
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: baseentityname,
        href: baseentityurl,
    },
];

const props = defineProps({
   categories: {
        type: Object,
        required: true,
    },
    locations: {
        type: Object,
        required: true,
    },
    manufacturers: {
        type: Object,
        required: true,
    },
    users: {
        type: Object,
        required: true,
    },
});



/* Define Props */
export interface Manufacturer {
    id: number; // Unique identifier for the Location
    category: any; // Category of the asset
    category_id: number; // Foreign key for category
    location: any; // Location of the asset
    location_id: number; // Foreign key for location
    manufacturer: any; // Manufacturer of the asset
    manufacturer_id: number; // Foreign key for manufacturer
    assignedTo: any; // User assigned to the asset
    assigned_to_user_id: number; // Foreign key for assigned user
    asset_tag: string; // Asset tag for identification
    name: string; // Name of the asset
    serial_number: string; // Serial number of the asset
    model_name: string; // Model name of the asset
    purchase_date: string; // Purchase date of the asset
    purchase_price: number; // Purchase price of the asset
    status: string; // Status of the asset (e.g., active, retired)
    notes: string; // Additional notes about the asset
}

const statusEnum = {
    Deployed: 'Deployed',
    InStorage: 'In Storage',
    Maintenance: 'Maintenance',
    Retired: 'Retired',
    Broken: 'Broken'
}

/* Define Table Columns */
const columns: ColumnDef<Manufacturer>[] = [
    {
        id: 'select', // Column for row selection
        header: ({ table }) =>
            h(Checkbox, {
                modelValue: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:modelValue': (value) => table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                modelValue: row.getIsSelected(),
                'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
                ariaLabel: 'Select row',
            }),
        enableSorting: false, // Disable sorting for this column
        enableHiding: false, // Disable hiding for this column
    },
    {
        accessorKey: 'name', // Column for Location name
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.getValue('name')),
    },
    {
        accessorKey: 'asset_tag', 
        header: 'Asset Tag',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.getValue('asset_tag')),
    },
    {
        accessorKey: 'category.name', 
        header: 'Category',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.original.category?.name || ''),
    },
    {
        accessorKey: 'location.name', 
        header: 'Location',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.original.location?.name || ''),
    },
    {
        accessorKey: 'manufacturer.name', 
        header: 'Manufacturer',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.original.manufacturer?.name || ''),
    },
    {
        accessorKey: 'assignedTo.name', 
        header: 'Assigned To User',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.original.assignedTo?.name || ''),
    },
     {
        accessorKey: 'status', 
        header: 'Status',
        cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.getValue('status')),
    },
    { 
        id: 'actions', // Column for row actions (edit/delete)
        enableHiding: false, // Disable hiding for this column
        cell: ({ row }) => {
            const rowitem = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(ReusableDropDownAction, {
                    rowitem,
                    onEdit: handleEdit, // Edit handler
                    onDelete: openDeleteDialog, // Delete handler
                }),
            );
        },
    },
];

/* Dialog State */
const showDialogForm = ref(false); // State for showing/hiding the form dialog
const mode = ref('create'); // Mode for the form (create/edit)
const itemID = ref<number | null>(null); // ID of the item being edited

    
const handleOpenDialogForm = () => {
    showDialogForm.value = true; // Show the form dialog
    mode.value = 'create'; // Set mode to create
};

/* Form Schema and Configuration */
const schema = z.object({
    name: z
        .string({
            required_error: 'Name is required',
            invalid_type_error: 'Name must be a string',
        })
        .toUpperCase()
        .min(3, {
            message: 'Name must be at least 3 characters long',
        }),
    serial_number: z
        .string({
            required_error: 'Serial number is required',
            invalid_type_error: 'Serial number must be a string',
        })
        .toUpperCase()
        .min(3, {
            message: 'Serial number must be at least 3 characters long',
        }),
    model_name: z
        .string({
            required_error: 'Model Name is required',
            invalid_type_error: 'Model Name must be a string',
        })
        .toUpperCase()
        .min(3, {
            message: 'Model Name must be at least 3 characters long',
        }),
    category_id: z.enum(
        props.categories.map((item:any) => item.name),
        {
            required_error: 'Category is required',
        }
    ),
    location_id: z.enum(props.locations.map((item:any) => item.name))
    .nullable().optional(),
    manufacturer_id:z.enum(props.manufacturers.map((item:any) => item.name))
    .nullable().optional(),
    assigned_to_user_id:z.enum(props.users.map((item:any) => item.name))
    .nullable().optional(),
    asset_tag: z
        .string({
            required_error: 'Asset tag is required',
            invalid_type_error: 'Asset Tag must be a string',
        })
        .toUpperCase()
        .min(3, {
            message: 'Asset tag must be at least 3 characters long',
        }),
    
    purchase_date: z.coerce.date().optional(),
    purchase_price: z.coerce.number()
    .nonnegative().optional(),
    status: z.enum(Object.values(statusEnum) as [string, ...string[]], {
        required_error: 'Status is required',
    }),
    notes: z.string().max(1000).nullable().optional()
});

const fieldconfig: any = {
    name: {
        label: 'Name',
        inputProps: {
            type: 'text',
            class: 'uppercase',
            placeholder: 'Enter asset name',
        }
    },
    serial_number: {
        label: 'Serial Number',
        inputProps: {
            type: 'text',
            class: 'uppercase',
            placeholder: 'Enter serial number',
        }
    },
    model_name: {
        label: 'Model Name',
        inputProps: {
            type: 'text',
            class: 'uppercase',
            placeholder: 'Enter model name',
        }
    },
    category_id: {
        label: 'Select Category',
        component: 'select',
        inputProps: {
            placeholder: 'Select a category',
        },
    },
    location_id: {
        label: 'Select Location',
        component: 'select',
        inputProps: {
            placeholder: 'Select a location',
        },
    },
    manufacturer_id: {
        label: 'Select Manufacturer',
        component: 'select',
        inputProps: {
            placeholder: 'Select a manufacturer',
        },
    },
    assigned_to_user_id: {
        label: 'Assign to User',
        component: 'select',
        inputProps: {
            placeholder: 'Select a user to assign',
        },
    },
    asset_tag: {
        label: 'Asset Tag',
        inputProps: {
            type: 'text',
            class: 'uppercase',
            placeholder: 'Enter asset tag',
        }
    },
    purchase_date: {
        label: 'Purchase Date',
        inputProps: {
            type: 'date',
        }
    },
    purchase_price: {
        label: 'Purchase Price',
        inputProps: {
            type: 'number',
            min: 0,
            step: '1',
            placeholder: 'Enter purchase price',
        }
    },
    status: {
        label: 'Status',
        component: 'select',
        inputProps: {
            placeholder: 'Select status',
        },
    },
    notes: {
        label: 'Notes',
        component: 'textarea',
        inputProps: {
            placeholder: 'Enter any additional notes (max 1000 characters)',
            rows: 4,
        }
    },

};

const form = useForm({
    validationSchema: toTypedSchema(schema), // Validation schema
    initialValues: {
        name: '',
        serial_number: '',
        model_name: '',
        category_id: null,
        location_id: null,
        manufacturer_id: null,
        assigned_to_user_id: null,
        asset_tag: '',
        purchase_date: null,
        purchase_price: 0,
        status: null,
        notes  : '',
    },
});

/* Form Handlers */
const resetForm = () => {
    form.resetForm(); // Reset the form
    itemID.value = null; // Clear the item ID
};

const tableRef = ref<InstanceType<typeof ReusableTable> | null>(null);

const onSubmit = async (values: any) => {
    try{
        const mappedValues = {
            ...values,
            category_id: props.categories.find((cat:any) => cat.name === values.category_id)?.id || null,
            location_id: props.locations.find((loc:any) => loc.name === values.location_id)?.id || null,
            manufacturer_id: props.manufacturers.find((man:any) => man.name === values.manufacturer_id)?.id || null,
            assigned_to_user_id: props.users.find((user:any) => user.name === values.assigned_to_user_id)?.id || null,
        }

        if (mode.value === 'create') {
            await axios.post(`${baseentityurl}`, mappedValues); // Create a new category
            toast.success(`${baseentityname} created successfully.`);
        } else if (mode.value === 'edit') {
            await axios.put(`${baseentityurl}/${itemID.value}`, mappedValues); // Update an existing category
            toast.success(`${baseentityname} updated successfully.`);
        }

        resetForm(); // Reset the form
        await tableRef.value?.fetchRows(); // Refresh the table data
        showDialogForm.value = false; // Hide the form dialog
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            form.setErrors(error.response.data.errors); // Set validation errors
            toast.error('Validation failed. Please check your input.');
        } else {
            toast.error('An unexpected error occurred.');
        }
    }
};

/* Edit Handler */
const handleEdit = async (id: number) => {
    try {
        mode.value = 'edit'; // Set mode to edit
        itemID.value = id; // Set the item ID
        const response = await axios.get(`${baseentityurl}/${id}`); // Fetch the item data
        const data = response.data;
        const mappedData = {
            ...data,
            category_id: data.category?.name || null,
            location_id: data.location?.name || null,
            manufacturer_id: data.manufacturer?.name || null,
            assigned_to_user_id: data.assigned_to?.name || null,
            purchase_date: data.purchase_date ? parseDate(data.purchase_date.slice(0,10)) : null,
        }
        form.setValues(mappedData); // Populate the form with the item data
        showDialogForm.value = true; // Show the form dialog
    } catch (error) {
        console.log(`Error fetching ${baseentityname} data:`, error);
        toast.error(`Failed to fetch ${baseentityname} data.`);
    }
};

/* Delete Dialog State */
const showDeleteDialog = ref(false); // State for showing/hiding the delete confirmation dialog
const itemIDToDelete = ref<number | null>(null); // ID of the item to be deleted

const openDeleteDialog = (id: number) => {
    itemIDToDelete.value = id; // Set the item ID to delete
    showDeleteDialog.value = true; // Show the delete confirmation dialog
};

const handleDelete = async () => {
    try {
        if (!itemIDToDelete.value) return;

        await axios.delete(`${baseentityurl}/${itemIDToDelete.value}`); // Delete the item
        toast.success(`${baseentityname} deleted successfully.`);
        await tableRef.value?.fetchRows(); // Refresh the table data
        showDeleteDialog.value = false; // Hide the delete confirmation dialog
    } catch (error) {
        console.log(`Error deleting ${baseentityname}:`, error);
        toast.error(`Failed to delete ${baseentityname}. Please try again.`);
    }
};

</script>

<template>
    <Head :title="baseentityname" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-4">
             <div class="flex items-center gap-2 py-2">
                <div class="ml-auto flex items-center gap-2">
                    <Button class="bg-cyan-500" @click="handleOpenDialogForm"> <Plus class="h-4"></Plus> Create {{ baseentityname }} </Button>
                </div>
            </div>

            <ReusableTable ref="tableRef" :columns="columns" :baseentityname="baseentityname" :baseentityurl="baseentityurl" />

              <!-- Dialog Form -->
             <Dialog v-model:open="showDialogForm">
                <DialogContent class="sm:max-w-[500px]" >
                    <DialogHeader>
                        <DialogTitle>{{ mode === 'create' ? 'Create' : 'Update' }} {{ baseentityname }}</DialogTitle>
                    </DialogHeader>
                    <DialogDescription> Use this form to edit the {{ baseentityname }} details. </DialogDescription>
                    <AutoForm class="space-y-6 max-h-[80vh] overflow-y-auto" :form="form" :schema="schema" :field-config="fieldconfig" @submit="onSubmit">
                        <DialogFooter>
                            <Button type="submit" class="bg-green-300">
                                {{ mode === 'create' ? 'Create' : 'Update' }}
                            </Button>
                        </DialogFooter>
                    </AutoForm>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <ReusableAlertDialog
                :open="showDeleteDialog"
                :title="`Delete ${baseentityname}`"
                :description="`Are you sure you want to delete this ${baseentityname}? This action cannot be undone.`"
                @confirm="handleDelete"
                @cancel="showDeleteDialog = false"
            />
        </div>
    </AppLayout>    
</template>