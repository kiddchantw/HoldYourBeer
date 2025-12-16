<?php

return [
    // Attributes
    'attributes' => [
        'name' => 'Brand Name',
    ],

    // Validation messages
    'validation' => [
        'name_required' => 'The :attribute field is required.',
        'name_unique' => 'The :attribute has already been taken.',
        'name_max' => 'The :attribute may not be greater than :max characters.',
    ],

    // Messages
    'messages' => [
        'created' => 'Brand created successfully.',
        'updated' => 'Brand updated successfully.',
        'deleted' => 'Brand deleted successfully.',
        'restored' => 'Brand restored successfully.',
        'force_deleted' => 'Brand permanently deleted.',
        'cannot_delete_has_beers' => 'Cannot delete this brand because it has :count associated beer(s). Please delete or reassign these beers first.',
        'error' => 'Operation failed, please try again later.',
    ],

    // Navigation
    'menu' => 'Brands',

    // Page Titles
    'titles' => [
        'index' => 'Brand Management',
        'create' => 'Create Brand',
        'edit' => 'Edit Brand',
    ],

    // Buttons
    'buttons' => [
        'create' => 'Create Brand',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'search' => 'Search',
        'clear' => 'Clear',
        'submit' => 'Save',
        'submitting' => 'Saving...',
        'cancel' => 'Cancel',
    ],

    // Table Headers
    'table' => [
        'id' => 'ID',
        'name' => 'Brand Name',
        'beers_count' => 'Beers Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
        'actions' => 'Actions',
    ],

    // Search
    'search' => [
        'placeholder' => 'Search brand name...',
        'results' => 'Found :count results for ":keyword"',
        'no_results' => 'No brands found.',
    ],

    // Confirm
    'confirm' => [
        'delete' => 'Are you sure you want to delete this brand?',
        'force_delete' => 'Are you sure you want to permanently delete this brand? This action cannot be undone!',
    ],
];
