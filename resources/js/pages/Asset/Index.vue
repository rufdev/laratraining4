<script setup lang="ts">
import ReusableDropDownAction from '@/components/entitycomponents/ReusableDropDownAction.vue'; // Dropdown for row actions (edit/delete)
import ReusableTable from '@/components/entitycomponents/ReusableTable.vue'; // Table component for displaying data
// import ReusableAlertDialog from '@/components/entitycomponents/ReusableAlertDialog.vue';
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
                    // onEdit: handleEdit, // Edit handler
                    // onDelete: openDeleteDialog, // Delete handler
                }),
            );
        },
    },
];


</script>

<template>
    <Head :title="baseentityname" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-4">
            <ReusableTable ref="tableRef" :columns="columns" :baseentityname="baseentityname" :baseentityurl="baseentityurl" />
        </div>
    </AppLayout>    
</template>