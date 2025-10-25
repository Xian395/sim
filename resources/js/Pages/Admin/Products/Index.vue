<template>
    <AdminAuthenticatedLayout>
        <div v-if="$page.props.auth.user.role === 'admin'" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div
                        class="flex items-center justify-between border-b border-stroke py-4 px-6.5 dark:border-strokedark p-6"
                    >
                        <h2 class="text-2xl font-black text-blue-700">
                            Product Management
                        </h2>

                        <div class="flex gap-2">
                            <ButtonNew
                                types="pdf"
                                size="md"
                                tooltips="Export Products to PDF"
                                @click="exportToPDF"
                            >
                                PDF
                            </ButtonNew>
                            <ButtonNew
                                types="add"
                                size="md"
                                tooltips="Add New Product"
                                @click="
                                    $inertia.visit(
                                        route('admin.products.create')
                                    )
                                "
                            >
                                Add
                            </ButtonNew>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="p-6 border-b bg-gray-50">
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 items-end"
                            >
                                <div
                                    class="sm:col-span-2 lg:col-span-2 xl:col-span-2"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Search</label
                                    >
                                    <input
                                        v-model="filters.search"
                                        @input="applyFilters"
                                        type="text"
                                        placeholder="Search products..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Category</label
                                    >
                                    <select
                                        v-model="filters.category"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">All Categories</option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Brand</label
                                    >
                                    <select
                                        v-model="filters.brand"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">All Brands</option>
                                        <option
                                            v-for="brand in brands"
                                            :key="brand.id"
                                            :value="brand.id"
                                        >
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Stock Status</label
                                    >
                                    <select
                                        v-model="filters.stockStatus"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">All Stock</option>
                                        <option value="out_of_stock">
                                            Out of Stock
                                        </option>
                                        <option value="low_stock">
                                            Low Stock
                                        </option>
                                        <option value="in_stock">
                                            In Stock
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Sort By</label
                                    >
                                    <select
                                        v-model="filters.sortBy"
                                        @change="applyFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="name">Name</option>
                                        <option value="item_code">
                                            Item Code
                                        </option>
                                        <option value="price_low">
                                            Price (lowest)
                                        </option>
                                        <option value="price_high">
                                            Price (highest)
                                        </option>
                                        <option value="stock_low">
                                            Stock (lowest)
                                        </option>
                                        <option value="stock_high">
                                            Stock (highest)
                                        </option>
                                        <option value="created_at_newest">
                                            Date Added (newest)
                                        </option>
                                        <option value="created_at_oldest">
                                            Date Added (oldest)
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Price Range
                                    </label>
                                    <div class="flex space-x-2">
                                        <input
                                            v-model="filters.priceMin"
                                            @input="applyFilters"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Min"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        <input
                                            v-model="filters.priceMax"
                                            @input="applyFilters"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Max"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end lg:justify-start">
                                    <button
                                        @click="clearFilters"
                                        class="w-full lg:w-auto px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded transition-colors whitespace-nowrap"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="mb-4 text-sm text-gray-600">
                                {{ filteredProducts.length }} products found
                                <span v-if="isFiltered">(filtered)</span>
                            </div>

                            <DataTable
                                :data="paginatedProducts"
                                :columns="tableColumns"
                                :empty-message="'No products found'"
                                item-key="id"
                                ref="dataTable"
                            >
                                <template #column-image_url="{ item }">
                                    <div class="flex justify-center">
                                        <div
                                            class="w-12 h-12 rounded-lg overflow-hidden shadow-lg relative cursor-pointer hover:shadow-xl transition-shadow duration-200"
                                            @click="viewImage(item)"
                                        >
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                :alt="item.name"
                                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-200"
                                                @error="handleImageError"
                                            />
                                            <div
                                                class="w-full h-full rounded-lg bg-gradient-to-br flex items-center justify-center absolute inset-0"
                                                :class="[
                                                    getProductGradient(item.id),
                                                    item.image_url
                                                        ? 'gradient-fallback hidden'
                                                        : '',
                                                ]"
                                            >
                                                <svg
                                                    class="w-6 h-6 text-white"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                    />
                                                </svg>
                                            </div>
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all duration-200 flex items-center justify-center"
                                            >
                                                <svg
                                                    class="w-4 h-4 text-white opacity-0 hover:opacity-100 transition-opacity duration-200"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    ></path>
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    ></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #column-name="{ item }">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ item.name }}
                                    </div>
                                    <div
                                        v-if="item.description"
                                        class="text-sm text-gray-500 truncate max-w-xs"
                                    >
                                        {{ item.description }}
                                    </div>
                                </template>

                                <template #column-price="{ item }">
                                    <div v-if="item.price" class="text-sm text-gray-900">
                                        ₱{{ parseFloat(item.price).toFixed(2) }}
                                    </div>
                                    <div v-else class="text-sm font-bold text-amber-600 bg-amber-100 px-2 py-1 rounded">
                                        No price set
                                    </div>
                                </template>

                                <template #column-categories="{ item }">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="category in item.categories"
                                            :key="category.id"
                                            :class="{
                                                'bg-blue-100 text-blue-800': category.status === 'active',
                                                'bg-orange-100 text-orange-800': category.status === 'inactive'
                                            }"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ category.name }}
                                            <span v-if="category.status === 'inactive'" class="ml-1" title="This category is archived">⚠️</span>
                                        </span>
                                        <span
                                            v-if="
                                                !item.categories ||
                                                item.categories.length === 0
                                            "
                                            class="text-gray-400 text-sm"
                                        >
                                            No categories
                                        </span>
                                    </div>
                                </template>

                                <template #column-brand="{ item }">
                                    <div>
                                        <span
                                            v-if="item.brand"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full"
                                        >
                                            {{ item.brand.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-gray-400 text-sm"
                                        >
                                            No brand
                                        </span>
                                    </div>
                                </template>

                                <template #column-stock_quantity="{ item }">
                                    <div class="text-sm text-gray-900">
                                        {{ item.stock_quantity || 0 }}
                                        <span
                                            v-if="
                                                (item.stock_quantity || 0) === 0
                                            "
                                            class="ml-1 text-red-600 text-xs"
                                            >Out of Stock</span
                                        >
                                        <span
                                            v-else-if="
                                                (item.stock_quantity || 0) < 11
                                            "
                                            class="ml-1 text-yellow-500 text-xs"
                                            >Low</span
                                        >
                                    </div>
                                </template>

                                <template #column-actions="{ item }">
                                    <div class="text-sm font-medium space-x-2">
                                        <ButtonCard>
                                            <template #edit>
                                                <ButtonNew
                                                    types="edit"
                                                    class="text-blue-600 hover:text-blue-900"
                                                    @click="openEditModal(item)"
                                                />
                                            </template>

                                            <template #delete>
                                                <ButtonNew
                                                    types="delete"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="
                                                        openDeleteConfirmation(
                                                            item
                                                        )
                                                    "
                                                />
                                            </template>
                                        </ButtonCard>
                                    </div>
                                </template>
                            </DataTable>
                            <div class="mt-4 flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Showing {{ startIndex + 1 }} to
                                    {{
                                        Math.min(
                                            startIndex + itemsPerPage,
                                            filteredProducts.length
                                        )
                                    }}
                                    of {{ filteredProducts.length }} results
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        v-if="currentPage > 1"
                                        @click="currentPage--"
                                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                                    >
                                        Previous
                                    </button>
                                    <span class="px-4 py-2 text-gray-700">
                                        Page {{ currentPage }} of
                                        {{ totalPages }}
                                    </span>
                                    <button
                                        v-if="currentPage < totalPages"
                                        @click="currentPage++"
                                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                >
                    Unauthorized: You do not have access to this page.
                </div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <Modal :show="showEditModal" @close="closeEditModal" max-width="xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Product</h3>
                    <button
                        @click="closeEditModal"
                        type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
                    >
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <!-- Item Code -->
                    <div>
                        <label for="edit_item_code" class="block mb-2 text-sm font-medium text-gray-900">Item Code</label>
                        <input
                            type="text"
                            id="edit_item_code"
                            v-model="editForm.item_code"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required
                        />
                        <div v-if="editForm.errors.item_code" class="text-red-600 text-sm mt-1">{{ editForm.errors.item_code }}</div>
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                        <input
                            type="text"
                            id="edit_name"
                            v-model="editForm.name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            required
                        />
                        <div v-if="editForm.errors.name" class="text-red-600 text-sm mt-1">{{ editForm.errors.name }}</div>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="edit_price" class="block mb-2 text-sm font-medium text-gray-900">Selling Price (₱)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            id="edit_price"
                            v-model="editForm.price"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="0.00"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-600">The price customers pay (You enter cost price when adding stock)</p>
                        <div v-if="editForm.errors.price" class="text-red-600 text-sm mt-1">{{ editForm.errors.price }}</div>
                    </div>

                    <!-- Categories -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Categories</label>

                        <!-- Selected Categories Tags -->
                        <div v-if="editSelectedCategories.length > 0" class="mb-3 flex flex-wrap gap-2">
                            <span
                                v-for="category in editSelectedCategories"
                                :key="category.id"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-300"
                            >
                                {{ category.name }}
                                <button
                                    type="button"
                                    @click="removeEditCategory(category.id)"
                                    class="ml-2 text-blue-600 hover:text-blue-800 focus:outline-none"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </span>
                        </div>

                        <!-- Add Category Button -->
                        <button
                            type="button"
                            @click="openEditCategoryModal"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            + Add Category
                        </button>

                        <div v-if="editForm.errors.category_ids" class="text-red-600 text-sm mt-2">{{ editForm.errors.category_ids }}</div>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Brand (Optional)</label>

                        <!-- Selected Brand Tag -->
                        <div v-if="editSelectedBrand" class="mb-3 flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                {{ editSelectedBrand.name }}
                                <button
                                    type="button"
                                    @click="removeEditBrand"
                                    class="ml-2 text-green-600 hover:text-green-800 focus:outline-none"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </span>
                        </div>

                        <!-- Add Brand Buttons -->
                        <div v-if="!editSelectedBrand" class="flex gap-2">
                            <button
                                type="button"
                                @click="openEditBrandModal"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Select Brand
                            </button>
                            <button
                                type="button"
                                @click="openQuickBrandModal"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 whitespace-nowrap"
                                title="Create New Brand"
                            >
                                + New
                            </button>
                        </div>

                        <div v-if="editForm.errors.brand_id" class="text-red-600 text-sm mt-2">{{ editForm.errors.brand_id }}</div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="edit_description" class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                        <input
                            type="text"
                            id="edit_description"
                            v-model="editForm.description"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        />
                        <div v-if="editForm.errors.description" class="text-red-600 text-sm mt-1">{{ editForm.errors.description }}</div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-2 pt-4 border-t">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="px-4 py-2 text-gray-700 bg-orange-500 hover:bg-orange-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                            <span class="mdi mdi-close-box"></span> Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <span class="mdi mdi-content-save-check"></span> {{ editForm.processing ? "Updating..." : "Save" }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-4 md:p-5 text-center">
                <svg
                    class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 20"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
                <h3
                    class="mb-5 text-lg font-normal text-black dark:text-gray-400"
                >
                    Are you sure you want to delete the product
                    <strong>"{{ productToDelete?.name }}"</strong>?
                    <br />
                    <span class="text-red-600"
                        >You won't be able to revert this!</span
                    >
                </h3>
                <button
                    @click="confirmDelete"
                    type="button"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center"
                >
                    Yes, I'm sure
                </button>
                <button
                    @click="closeDeleteModal"
                    type="button"
                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                >
                    No, cancel
                </button>
            </div>
        </Modal>
        <!-- Image View Modal -->
        <Modal :show="showImageModal" @close="closeImageModal" max-width="2xl">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ selectedImage?.name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ selectedImage?.item_code }}
                        </p>
                    </div>
                    <button
                        @click="closeImageModal"
                        type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
                    >
                        <svg
                            class="w-3 h-3"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 14 14"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                            />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <div class="flex justify-center bg-gray-50 rounded-lg p-4">
                    <div class="relative max-w-full max-h-96">
                        <img
                            v-if="selectedImage?.image_url"
                            :src="selectedImage.image_url"
                            :alt="selectedImage?.name"
                            class="max-w-full max-h-96 object-contain rounded-lg shadow-lg"
                            @error="handleImageError"
                        />
                        <div
                            v-else
                            class="w-64 h-64 rounded-lg bg-gradient-to-br flex items-center justify-center"
                            :class="getProductGradient(selectedImage?.id || 0)"
                        >
                            <svg
                                class="w-16 h-16 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <div class="flex justify-center space-x-4">
                        <div v-if="selectedImage?.price" class="text-sm text-gray-600">
                            <span class="font-medium">Price:</span>
                            ₱{{ parseFloat(selectedImage.price).toFixed(2) }}
                        </div>
                        <div v-else class="text-sm font-bold text-amber-600">
                            <span class="font-medium">Price:</span> No price set
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Stock:</span>
                            {{ selectedImage?.stock_quantity || 0 }}
                        </div>
                    </div>
                    <p
                        v-if="selectedImage?.description"
                        class="text-sm text-gray-600 mt-2"
                    >
                        {{ selectedImage.description }}
                    </p>
                </div>
            </div>
        </Modal>

        <!-- Quick Brand Creation Modal -->
        <Modal :show="showQuickBrandModal" @close="closeQuickBrandModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Add New Brand
                    </h3>
                    <button
                        @click="closeQuickBrandModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createQuickBrand">
                    <div class="mb-4">
                        <label for="quick_brand_name" class="block mb-2 text-sm font-medium text-gray-900">Brand Name</label>
                        <input
                            id="quick_brand_name"
                            type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            v-model="quickBrandForm.name"
                            required
                            autofocus
                        />
                        <div v-if="quickBrandForm.errors.name" class="text-red-600 text-sm mt-1">
                            {{ quickBrandForm.errors.name }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="quick_brand_description" class="block mb-2 text-sm font-medium text-gray-900">Description (Optional)</label>
                        <textarea
                            id="quick_brand_description"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            rows="3"
                            v-model="quickBrandForm.description"
                        ></textarea>
                        <div v-if="quickBrandForm.errors.description" class="text-red-600 text-sm mt-1">
                            {{ quickBrandForm.errors.description }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button
                            type="button"
                            @click="closeQuickBrandModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="quickBrandForm.processing"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {{ quickBrandForm.processing ? 'Creating...' : 'Create Brand' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit - Category Selection Modal -->
        <CategorySelectionModal
            :show="showEditCategoryModal"
            :categories="categories"
            :selectedCategories="editSelectedCategories"
            @close="closeEditCategoryModal"
            @toggle="toggleEditCategoryFromModal"
        />

        <!-- Edit - Brand Selection Modal -->
        <BrandSelectionModal
            :show="showEditBrandModal"
            :brands="availableBrands"
            :selectedBrand="editSelectedBrand"
            @close="closeEditBrandModal"
            @select="selectEditBrandFromModal"
        />
    </AdminAuthenticatedLayout>
</template>

<script setup>
import { usePage, Link, router, useForm } from "@inertiajs/vue3";
import AdminAuthenticatedLayout from "@/Layouts/AdminAuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import DataTable from "@/Components/DataTable.vue";
import { defineProps, ref, computed, watch } from "vue";
import { notify, getLogoBase64 } from "@/globalFunctions.js";
import ButtonNew from "@/Components/ButtonNew.vue";
import ButtonCard from "@/Components/ButtonCard.vue";
import CategorySelectionModal from "./CategorySelectionModal.vue";
import BrandSelectionModal from "./BrandSelectionModal.vue";
import { jsPDF } from "jspdf";
import { autoTable } from "jspdf-autotable";

const props = defineProps({
    products: Array,
    categories: Array,
    allCategories: Array,
    brands: Array,
});

const $page = usePage();
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingProduct = ref(null);
const productToDelete = ref(null);
const dataTable = ref();

// Quick Brand Modal
const showQuickBrandModal = ref(false);
const availableBrands = ref([...props.brands]);

// Edit Modal - Categories & Brand
const editSelectedCategories = ref([]);
const showEditCategoryModal = ref(false);
const editSelectedBrand = ref(null);
const showEditBrandModal = ref(false);
const editCategorySearch = ref("");
const editBrandSearch = ref("");
const editFilteredCategories = ref([]);
const editFilteredBrands = ref([]);
const showEditCategoryDropdown = ref(false);
const showEditBrandDropdown = ref(false);

const filters = ref({
    search: "",
    category: "",
    brand: "",
    stockStatus: "",
    sortBy: "name",
    priceMin: "",
    priceMax: "",
});

const currentPage = ref(1);
const itemsPerPage = ref(10);

const viewImage = (product) => {
    selectedImage.value = product;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImage.value = null;
};
const showImageModal = ref(false);
const selectedImage = ref(null);

const handleImageError = (event) => {
    event.target.style.display = "none";
    const fallback = event.target.nextElementSibling;
    if (fallback && fallback.classList.contains("gradient-fallback")) {
        fallback.classList.remove("hidden");
    }
};

const getProductGradient = (productId) => {
    const gradients = [
        "from-blue-400 to-blue-600",
        "from-green-400 to-green-600",
        "from-purple-400 to-purple-600",
        "from-red-400 to-red-600",
        "from-yellow-400 to-yellow-600",
        "from-indigo-400 to-indigo-600",
        "from-pink-400 to-pink-600",
        "from-teal-400 to-teal-600",
    ];
    return gradients[productId % gradients.length];
};

const filteredProducts = computed(() => {
    let result = [...(props.products || [])];

    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        result = result.filter(
            (product) =>
                product.name.toLowerCase().includes(searchTerm) ||
                product.item_code.toLowerCase().includes(searchTerm) ||
                product.barcode.toLowerCase().includes(searchTerm) ||
                (product.description &&
                    product.description.toLowerCase().includes(searchTerm))
        );
    }

    if (filters.value.category) {
        result = result.filter(
            (product) =>
                product.categories &&
                product.categories.some(
                    (category) => category.id == filters.value.category
                )
        );
    }

    if (filters.value.brand) {
        result = result.filter(
            (product) =>
                product.brand && product.brand.id == filters.value.brand
        );
    }

    if (filters.value.priceMin || filters.value.priceMax) {
        result = result.filter((product) => {
            const price = parseFloat(product.price) || 0;
            const minPrice = filters.value.priceMin
                ? parseFloat(filters.value.priceMin)
                : 0;
            const maxPrice = filters.value.priceMax
                ? parseFloat(filters.value.priceMax)
                : Infinity;
            return price >= minPrice && price <= maxPrice;
        });
    }

    if (filters.value.stockStatus) {
        result = result.filter((product) => {
            const stock = product.stock_quantity || 0;
            switch (filters.value.stockStatus) {
                case "out_of_stock":
                    return stock === 0;
                case "low_stock":
                    return stock > 0 && stock < 11;
                case "in_stock":
                    return stock >= 11;
                default:
                    return true;
            }
        });
    }

    result.sort((a, b) => {
        let aValue, bValue;

        switch (filters.value.sortBy) {
            case "price_low":
                aValue = parseFloat(a.price) || 0;
                bValue = parseFloat(b.price) || 0;
                return aValue - bValue;

            case "price_high":
                aValue = parseFloat(a.price) || 0;
                bValue = parseFloat(b.price) || 0;
                return bValue - aValue;

            case "stock_low":
                aValue = parseInt(a.stock_quantity) || 0;
                bValue = parseInt(b.stock_quantity) || 0;
                return aValue - bValue;

            case "stock_high":
                aValue = parseInt(a.stock_quantity) || 0;
                bValue = parseInt(b.stock_quantity) || 0;
                return bValue - aValue;

            case "created_at_newest":
                aValue = new Date(a.created_at);
                bValue = new Date(b.created_at);
                return bValue - aValue;

            case "created_at_oldest":
                aValue = new Date(a.created_at);
                bValue = new Date(b.created_at);
                return aValue - bValue;

            default:
                aValue = a[filters.value.sortBy];
                bValue = b[filters.value.sortBy];

                if (filters.value.sortBy.includes(".")) {
                    const keys = filters.value.sortBy.split(".");
                    aValue = keys.reduce((obj, key) => obj?.[key], a);
                    bValue = keys.reduce((obj, key) => obj?.[key], b);
                }

                if (aValue == null) aValue = "";
                if (bValue == null) bValue = "";

                if (typeof aValue === "string") {
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                }

                if (typeof aValue === "number" && typeof bValue === "number") {
                    return filters.value.sortOrder === "desc"
                        ? bValue - aValue
                        : aValue - bValue;
                }

                let comparison = 0;
                if (aValue > bValue) comparison = 1;
                if (aValue < bValue) comparison = -1;

                return filters.value.sortOrder === "desc"
                    ? -comparison
                    : comparison;
        }
    });

    return result;
});

const totalPages = computed(() =>
    Math.ceil(filteredProducts.value.length / itemsPerPage.value)
);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const paginatedProducts = computed(() => {
    return filteredProducts.value.slice(
        startIndex.value,
        startIndex.value + itemsPerPage.value
    );
});

const isFiltered = computed(() => {
    return (
        filters.value.search ||
        filters.value.category ||
        filters.value.brand ||
        filters.value.stockStatus ||
        filters.value.priceMin ||
        filters.value.priceMax
    );
});

watch(
    () => filteredProducts.value.length,
    () => {
        currentPage.value = 1;
    }
);

const applyFilters = () => {
    currentPage.value = 1;
};

const clearFilters = () => {
    filters.value = {
        search: "",
        category: "",
        brand: "",
        stockStatus: "",
        sortBy: "name",
        priceMin: "",
        priceMax: "",
    };
    currentPage.value = 1;
};

const currentProductInactiveCategories = computed(() => {
    if (!editingProduct.value || !props.allCategories) {
        return [];
    }

    const productCategoryIds = editingProduct.value.categories
        ? editingProduct.value.categories.map(c => c.id)
        : [];

    return props.allCategories.filter(category =>
        category.status === 'inactive' &&
        productCategoryIds.includes(category.id)
    );
});

// Edit Modal - Computed properties for autocomplete
const editDisplayedCategories = computed(() => {
    return editFilteredCategories.value.slice(0, 5);
});

const editDisplayedBrands = computed(() => {
    return editFilteredBrands.value.slice(0, 5);
});

const tableColumns = computed(() => [
    {
        key: "item_code",
        label: "Item Code",
        type: "text",
        align: "left",
    },
    {
        key: "image_url",
        label: "Image",
        type: "custom",
        align: "center",
    },
    {
        key: "name",
        label: "Name",
        type: "custom",
        align: "left",
    },
    {
        key: "price",
        label: "Price",
        type: "custom",
        align: "left",
    },
    {
        key: "categories",
        label: "Categories",
        type: "custom",
        align: "left",
    },
    {
        key: "brand",
        label: "Brand",
        type: "custom",
        align: "left",
    },
    {
        key: "stock_quantity",
        label: "Stock",
        type: "custom",
        align: "left",
    },
    {
        key: "actions",
        label: "Actions",
        type: "custom",
        align: "right",
    },
]);

const editForm = useForm({
    name: "",
    barcode: "",
    price: "",
    item_code: "",
    category_ids: [],
    brand_id: "",
    description: "",
});

const quickBrandForm = useForm({
    name: "",
    description: "",
});

const openEditModal = (product) => {
    editingProduct.value = product;
    editForm.name = product.name;
    editForm.barcode = product.barcode;
    editForm.price = product.price;
    editForm.item_code = product.item_code;
    editForm.category_ids = product.categories
        ? product.categories.map((c) => c.id)
        : []; // Updated
    editForm.brand_id = product.brand ? product.brand.id : "";
    editForm.description = product.description || "";

    // Populate autocomplete selections
    editSelectedCategories.value = product.categories || [];
    editSelectedBrand.value = product.brand || null;
    editCategorySearch.value = "";
    editBrandSearch.value = "";
    editFilteredCategories.value = [...props.categories];
    editFilteredBrands.value = [...availableBrands.value];

    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingProduct.value = null;
    editForm.reset();
    editForm.clearErrors();

    // Reset autocomplete state
    editSelectedCategories.value = [];
    editSelectedBrand.value = null;
    editCategorySearch.value = "";
    editBrandSearch.value = "";
    editFilteredCategories.value = [];
    editFilteredBrands.value = [];
    showEditCategoryDropdown.value = false;
    showEditBrandDropdown.value = false;
};

const openDeleteConfirmation = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

const confirmDelete = async () => {
    if (!productToDelete.value) return;

    try {
        await router.delete(
            route("admin.products.destroy", productToDelete.value.id),
            {
                onSuccess: () => {
                    reload();
                    notify("Product deleted successfully!", "success");
                    closeDeleteModal();
                },
                onError: (errors) => {
                    console.error("Delete error:", errors);
                    notify(
                        "Failed to delete product. Please try again.",
                        "error"
                    );
                    closeDeleteModal();
                },
            }
        );
    } catch (error) {
        console.error("Unexpected error during deletion:", error);
        notify(
            "An unexpected error occurred while deleting the product.",
            "error"
        );
        closeDeleteModal();
    }
};

const reload = () => {
    router.reload({
        only: ["products", "categories"],
        preserveState: true,
        preserveScroll: true,
    });
};

const submitEdit = () => {
    if (!editingProduct.value) return;

    editForm.put(route('admin.products.update', editingProduct.value.id), {
        onSuccess: () => {
            closeEditModal();
            notify("Product updated successfully!", "success");
            reload();
        },
        onError: () => {
            notify(
                "Failed to update product. Please check the form fields.",
                "error"
            );
        },
    });
};

const exportToPDF = async () => {
    const doc = new jsPDF();

    const logoBase64 = await getLogoBase64();

    const pageWidth = doc.internal.pageSize.width;
    let yPosition = 8;

    if (logoBase64) {
        const logoWidth = 20;
        const logoHeight = 20;
        const logoX = (pageWidth - logoWidth) / 2;

        doc.addImage(
            logoBase64,
            "PNG",
            logoX,
            yPosition,
            logoWidth,
            logoHeight
        );
        yPosition += logoHeight + 2;
    }

    doc.setFontSize(12);
    doc.setFont(undefined, "bold");
    const title = "PRODUCT LIST";
    const titleWidth = doc.getTextWidth(title);
    const titleX = (pageWidth - titleWidth) / 2;
    doc.text(title, titleX, yPosition);

    yPosition += 4;

    doc.setFont(undefined, "normal");

    doc.setFontSize(10);
    const totalProductsText = `Total Products: ${filteredProducts.value.length}`;

    if (isFiltered.value) {
        const filteredText = "[Filtered Results]";
        const filteredTextWidth = doc.getTextWidth(filteredText);
        const totalProductsWidth = doc.getTextWidth(totalProductsText);

        doc.text(filteredText, pageWidth - filteredTextWidth - 20, yPosition);

        doc.text(
            totalProductsText,
            pageWidth - filteredTextWidth - totalProductsWidth - 145,
            yPosition
        );
    } else {
        const totalProductsWidth = doc.getTextWidth(totalProductsText);
        doc.text(
            totalProductsText,
            pageWidth - totalProductsWidth - 20,
            yPosition
        );
    }

    yPosition += 5;

    const tableData = filteredProducts.value.map((product) => [
        product.item_code || "",
        product.name || "",
        product.categories && product.categories.length > 0
            ? product.categories.map((cat) => cat.name).join(", ")
            : "No categories",
        product.price ? `${parseFloat(product.price).toFixed(2)}` : "No price",
        product.stock_quantity || 0,
        getStockStatus(product.stock_quantity || 0),
        product.description || "",
    ]);

    const table = autoTable(doc, {
        head: [
            [
                "Item Code",
                "Name",
                "Categories",
                "Price(PHP)",
                "Stock",
                "Status",
                "Description",
            ],
        ],
        body: tableData,
        startY: yPosition,
        styles: {
            fontSize: 8,
            cellPadding: 2,
        },
        headStyles: {
            fillColor: [59, 130, 246],
            textColor: 255,
            fontStyle: "bold",
        },
        columnStyles: {
            0: { cellWidth: 20 },
            1: { cellWidth: 30 },
            2: { cellWidth: 30 },
            3: { cellWidth: 20 },
            4: { cellWidth: 15 },
            5: { cellWidth: 20 },
            6: { cellWidth: 40 },
        },
        didParseCell: function (data) {
            if (data.column.index === 5) {
                if (data.cell.text[0] === "Out of Stock") {
                    data.cell.styles.textColor = [220, 38, 38];
                } else if (data.cell.text[0] === "Low Stock") {
                    data.cell.styles.textColor = [245, 101, 101];
                } else if (data.cell.text[0] === "In Stock") {
                    data.cell.styles.textColor = [34, 197, 94];
                }
            }
        },
    });

    const finalY = doc.lastAutoTable.finalY || 50;
    doc.setFontSize(10);
    const generatedText = `Generated on: ${new Date().toLocaleString()}`;
    doc.text(
        generatedText,
        pageWidth - doc.getTextWidth(generatedText) - 20,
        finalY + 15
    );

    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(
            `Page ${i} of ${pageCount}`,
            doc.internal.pageSize.width - 30,
            doc.internal.pageSize.height - 10
        );
    }

    const pdfBlob = doc.output("blob");
    const pdfUrl = URL.createObjectURL(pdfBlob);
    window.open(pdfUrl, "_blank");

    // notify('Products PDF opened in new tab!', 'success');
};

const getStockStatus = (stock) => {
    if (stock === 0) return "Out of Stock";
    if (stock < 11) return "Low Stock";
    return "In Stock";
};

const copyBarcode = async (barcode) => {
    try {
        await navigator.clipboard.writeText(barcode);
        notify("Barcode copied to clipboard!", "success");
    } catch (err) {
        const textArea = document.createElement("textarea");
        textArea.value = barcode;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("copy");
        document.body.removeChild(textArea);
        notify("Barcode copied to clipboard!", "success");
    }
};

const printBarcode = (product) => {
    const printWindow = window.open("", "_blank");
    const barcodeHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Barcode - ${product.name}</title>
      <style>
        body { 
          font-family: Arial, sans-serif; 
          text-align: center; 
          padding: 20px;
        }
        .barcode-container { 
          border: 1px solid #ccc; 
          padding: 20px; 
          display: inline-block;
          margin: 10px;
        }
        .barcode-visual {
          margin-bottom: 10px;
        }
        .barcode-number { 
          font-family: 'Courier New', monospace; 
          font-size: 14px; 
          margin: 5px 0;
        }
        .product-name { 
          font-size: 12px; 
          color: #666; 
          margin-top: 5px;
        }
        @media print {
          body { margin: 0; }
          .no-print { display: none; }
        }
      </style>
    </head>
    <body>
      <div class="barcode-container">
        <div class="barcode-visual">
          <svg width="200" height="50">
            ${generateBarcodeStripes(product.barcode)}
          </svg>
        </div>
        <div class="barcode-number">${product.barcode}</div>
        <div class="product-name">${product.name}</div>
        <div class="product-name">${product.price ? '₱' + parseFloat(product.price).toFixed(2) : 'No price set'}</div>
      </div>
      <div class="no-print" style="margin-top: 20px;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
      </div>
    </body>
    </html>
  `;

    printWindow.document.write(barcodeHTML);
    printWindow.document.close();
};

const generateBarcodeStripes = (barcode) => {
    let stripes = "";
    for (let i = 0; i < barcode.length; i++) {
        const digit = parseInt(barcode[i]);
        const width = digit % 2 === 0 ? 3 : 6;
        const x = i * 15;
        stripes += `<rect x="${x}" y="0" width="${width}" height="40" fill="${
            digit % 2 === 0 ? "#000" : "#333"
        }"/>`;
    }
    return stripes;
};

// Quick Brand Modal Functions
const openQuickBrandModal = () => {
    showQuickBrandModal.value = true;
    quickBrandForm.reset();
    quickBrandForm.clearErrors();
};

const closeQuickBrandModal = () => {
    showQuickBrandModal.value = false;
    quickBrandForm.reset();
    quickBrandForm.clearErrors();
};

const createQuickBrand = async () => {
    try {
        const response = await axios.post(route('admin.brands.store'), quickBrandForm.data());

        if (response.data.success) {
            // Add the new brand to the available brands list
            const newBrand = response.data.brand;
            availableBrands.value.push(newBrand);

            // Auto-select the newly created brand in edit form
            if (editingProduct.value) {
                editForm.brand_id = newBrand.id;
                editSelectedBrand.value = newBrand;
            }

            // Close modal and notify
            closeQuickBrandModal();
            notify('Brand created successfully and selected!', 'success');
        } else {
            notify('Failed to create brand', 'error');
        }
    } catch (error) {
        console.error('Quick brand creation error:', error);
        if (error.response && error.response.data.errors) {
            quickBrandForm.setError(error.response.data.errors);
            const errorMessages = Object.values(error.response.data.errors).flat().join('<br>');
            notify(errorMessages, 'error');
        } else {
            notify('Something went wrong while creating the brand.', 'error');
        }
    }
};

// ========== Edit Modal - Category Functions ==========

const removeEditCategory = (categoryId) => {
    editSelectedCategories.value = editSelectedCategories.value.filter(
        (c) => c.id !== categoryId
    );
    editForm.category_ids = editForm.category_ids.filter(
        (id) => id !== categoryId
    );
};

const openEditCategoryModal = () => {
    showEditCategoryModal.value = true;
};

const closeEditCategoryModal = () => {
    showEditCategoryModal.value = false;
};

const toggleEditCategoryFromModal = (category) => {
    const index = editSelectedCategories.value.findIndex((c) => c.id === category.id);
    if (index > -1) {
        removeEditCategory(category.id);
    } else {
        editSelectedCategories.value.push(category);
        editForm.category_ids.push(category.id);
    }
};

// ========== Edit Modal - Brand Functions ==========

const selectEditBrand = (brand) => {
    editSelectedBrand.value = brand;
    editForm.brand_id = brand.id;
};

const removeEditBrand = () => {
    editSelectedBrand.value = null;
    editForm.brand_id = "";
};

const openEditBrandModal = () => {
    showEditBrandModal.value = true;
};

const closeEditBrandModal = () => {
    showEditBrandModal.value = false;
};

const selectEditBrandFromModal = (brand) => {
    selectEditBrand(brand);
    closeEditBrandModal();
};
</script>
