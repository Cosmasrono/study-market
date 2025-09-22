@extends('layouts.admin')

@section('title', 'Upload New Books')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6 text-maroon-800">Upload New Books</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6" role="alert">
            {!! nl2br(e(session('warning'))) !!}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            {!! nl2br(e(session('error'))) !!}
        </div>
    @endif

    <form action="{{ route('admin.books.upload') }}" method="POST" enctype="multipart/form-data" class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md border border-maroon-200">
        @csrf
        
        <!-- Title -->
        <div class="mb-4">
            <label for="title" class="block text-maroon-700 font-bold mb-2">Default Book Title *</label>
            <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                   class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500 @error('title') border-red-500 @enderror">
            <p class="text-sm text-maroon-600 mt-1">This will be used as the base title for uploaded books</p>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-maroon-700 font-bold mb-2">Description (Optional)</label>
            <textarea name="description" id="description" rows="4" 
                      class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Book Files -->
        <div class="mb-4">
            <label for="book_files" class="block text-maroon-700 font-bold mb-2">Book Files (Optional, Max 200MB per file)</label>
            <input type="file" name="book_files[]" id="book_files" multiple
                   accept=".pdf,.epub,.mobi,.docx,.txt" 
                   class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500 @error('book_files') border-red-500 @enderror">
            <p class="text-sm text-maroon-600 mt-1">Supported formats: PDF, EPUB, MOBI, DOCX, TXT</p>
            @error('book_files')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <div id="file-size-warning" class="text-red-500 text-sm mt-1 hidden">
                One or more files exceed the 200MB limit. Please choose smaller files.
            </div>
            <div id="file-list" class="mt-2 text-sm text-maroon-600"></div>
        </div>

        <!-- Individual Book Titles (Optional) -->
        <div id="individual_titles_container" class="mb-4" style="display: none;">
            <label class="block text-maroon-700 font-bold mb-2">Individual Book Titles (Optional)</label>
            <div id="individual_titles_list"></div>
            <p class="text-sm text-maroon-600 mt-1">Provide unique titles for each uploaded book. If left blank, the default title will be used.</p>
        </div>

        <!-- Book Type Selection -->
        <div class="mb-6">
            <label class="block text-maroon-700 font-bold mb-3">Book Type *</label>
            <div class="space-y-3">
                <!-- Free Book Option -->
                <div class="flex items-center p-3 border border-maroon-300 rounded-md hover:bg-maroon-50 transition-colors">
                    <input type="radio" name="book_type" id="free_book" value="free" 
                           {{ old('book_type', 'free') == 'free' ? 'checked' : '' }}
                           class="mr-3 text-maroon-600 focus:ring-maroon-500" required>
                    <label for="free_book" class="flex-1 cursor-pointer">
                        <div class="font-medium text-maroon-900">Free Books</div>
                        <div class="text-sm text-maroon-600">All books will be accessible to authenticated users without payment</div>
                    </label>
                    <div class="text-green-600 font-semibold bg-green-100 px-2 py-1 rounded">FREE</div>
                </div>

                <!-- Paid Book Option -->
                <div class="flex items-center p-3 border border-maroon-300 rounded-md hover:bg-maroon-50 transition-colors">
                    <input type="radio" name="book_type" id="paid_book" value="paid" 
                           {{ old('book_type') == 'paid' ? 'checked' : '' }}
                           class="mr-3 text-maroon-600 focus:ring-maroon-500" required>
                    <label for="paid_book" class="flex-1 cursor-pointer">
                        <div class="font-medium text-maroon-900">Paid Books</div>
                        <div class="text-sm text-maroon-600">All books will require payment for users to access</div>
                    </label>
                    <div class="text-blue-600 font-semibold bg-blue-100 px-2 py-1 rounded">PAID</div>
                </div>
            </div>
            @error('book_type')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Price (shown only for paid books) -->
        <div class="mb-4" id="price_section" style="display: none;">
            <label for="price" class="block text-maroon-700 font-bold mb-2">Price (KSh) *</label>
            <div class="relative">
                <span class="absolute left-3 top-2 text-maroon-600">KSh</span>
                <input type="number" name="price" id="price" min="0.01" step="0.01" 
                       value="{{ old('price') }}" placeholder="0.00"
                       class="w-full pl-12 pr-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500 @error('price') border-red-500 @enderror">
            </div>
            <p class="text-sm text-maroon-600 mt-1">This price will be applied to all uploaded books</p>
            @error('price')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Availability -->
        <div class="mb-6">
            <label for="is_available" class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_available" id="is_available" value="1" 
                       {{ old('is_available', true) ? 'checked' : '' }} 
                       class="mr-2 text-maroon-600 focus:ring-maroon-500">
                <span class="text-maroon-700 font-medium">Make all books available immediately</span>
            </label>
            <p class="text-sm text-maroon-600 mt-1 ml-6">Uncheck to save all as drafts (users won't see them)</p>
        </div>

        <!-- Upload Summary -->
        <div class="mb-6 p-4 bg-maroon-50 rounded-lg border border-maroon-200" id="upload_summary">
            <h4 class="font-semibold text-maroon-800 mb-2">Upload Summary</h4>
            <div id="summary_content">
                <p class="text-sm text-maroon-600">
                    <span class="font-medium">Books to upload:</span> 
                    <span id="summary_count" class="text-maroon-800 font-semibold">0</span>
                </p>
                <p class="text-sm text-maroon-600">
                    <span class="font-medium">Type:</span> 
                    <span id="summary_type" class="text-green-600 font-semibold">Free Books</span>
                </p>
                <p class="text-sm text-maroon-600" id="summary_price_line" style="display: none;">
                    <span class="font-medium">Price per book:</span> 
                    <span id="summary_price" class="text-blue-600 font-semibold">KSh 0.00</span>
                </p>
                <p class="text-sm text-maroon-600">
                    <span class="font-medium">Access:</span> 
                    <span id="summary_access">All authenticated users</span>
                </p>
                <p class="text-sm text-maroon-600">
                    <span class="font-medium">Status:</span> 
                    <span id="summary_status" class="text-maroon-800 font-semibold">Available immediately</span>
                </p>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div id="upload-progress" class="mb-6 hidden">
            <div class="bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-maroon-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-sm text-maroon-600 mt-2" id="progress-text">Preparing upload...</p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-between space-x-3">
            <a href="{{ route('admin.books') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                Cancel
            </a>
            <button type="submit" id="submit_btn"
                    class="bg-maroon-600 hover:bg-maroon-700 text-white px-6 py-2 rounded-md transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                <span id="submit_text">Upload Books</span>
                <span id="submit_loader" class="hidden">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookFileInput = document.getElementById('book_files');
    const freeBookRadio = document.getElementById('free_book');
    const paidBookRadio = document.getElementById('paid_book');
    const priceSection = document.getElementById('price_section');
    const priceInput = document.getElementById('price');
    const fileSizeWarning = document.getElementById('file-size-warning');
    const submitBtn = document.getElementById('submit_btn');
    const submitText = document.getElementById('submit_text');
    const submitLoader = document.getElementById('submit_loader');
    const individualTitlesContainer = document.getElementById('individual_titles_container');
    const individualTitlesList = document.getElementById('individual_titles_list');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');

    // Summary elements
    const summaryCount = document.getElementById('summary_count');
    const summaryType = document.getElementById('summary_type');
    const summaryPrice = document.getElementById('summary_price');
    const summaryPriceLine = document.getElementById('summary_price_line');
    const summaryAccess = document.getElementById('summary_access');
    const summaryStatus = document.getElementById('summary_status');

    let selectedFiles = [];

    // Handle upload mode change
    // This part is no longer needed as upload_mode is removed, but keeping it for now
    // uploadModeRadios.forEach(radio => {
    //     radio.addEventListener('change', function() {
    //         if (this.value === 'individual') {
    //             titleLabel.textContent = 'Base Title (Template)';
    //             titleHelp.textContent = 'This will be used as a template. You can customize individual titles below.';
    //             if (selectedFiles.length > 0) {
    //                 showIndividualTitles();
    //             }
    //         } else {
    //             titleLabel.textContent = 'Title *';
    //             titleHelp.textContent = 'This will be used as the title for all books';
    //             hideIndividualTitles();
    //         }
    //     });
    // });

    // File selection and validation
    bookFileInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        selectedFiles = files;
        
        let allValid = true;
        const maxSize = 200 * 1024 * 1024; // 200MB
        const maxFiles = 10; // This limit is now handled by the browser's file input
        const allowedExtensions = ['pdf', 'epub', 'mobi', 'docx', 'txt'];

        // Clear previous displays
        document.getElementById('file-list').innerHTML = '';
        individualTitlesList.innerHTML = ''; // Clear previous individual titles
        
        // Check file count
        if (files.length > maxFiles) {
            allValid = false;
            fileSizeWarning.textContent = `Too many files selected. Maximum is ${maxFiles} files.`;
            fileSizeWarning.classList.remove('hidden');
        } else {
            fileSizeWarning.classList.add('hidden');
        }

        // Process each file
        files.forEach((file, index) => {
            const fileListItem = document.createElement('li');
            fileListItem.textContent = `${file.name}: ${(file.size / 1024 / 1024).toFixed(2)}MB`;
            document.getElementById('file-list').appendChild(fileListItem);

            // Individual title input
            const titleInputWrapper = document.createElement('div');
            titleInputWrapper.className = 'mb-2';
            
            const label = document.createElement('label');
            label.textContent = `Title for ${file.name}`;
            label.className = 'block text-sm text-maroon-700 mb-1';
            
            const titleInput = document.createElement('input');
            titleInput.type = 'text';
            titleInput.name = 'individual_titles[]';
            titleInput.placeholder = 'Optional unique title';
            titleInput.className = 'w-full px-3 py-2 border border-maroon-300 rounded-md text-sm';
            
            titleInputWrapper.appendChild(label);
            titleInputWrapper.appendChild(titleInput);
            individualTitlesList.appendChild(titleInputWrapper);

            // File size validation
            const fileSize = file.size;
            const maxSize = 200 * 1024 * 1024; // 200MB in bytes

            if (fileSize > maxSize) {
                allValid = false;
                const sizeWarning = document.createElement('p');
                sizeWarning.textContent = `${file.name}: File size exceeds 200MB limit.`;
                sizeWarning.className = 'text-red-500 text-sm';
                document.getElementById('file-list').appendChild(sizeWarning);
            }
        });

        // Show/hide individual titles container based on file count
        if (files.length > 1) {
            individualTitlesContainer.style.display = 'block';
        } else {
            individualTitlesContainer.style.display = 'none';
        }

        // Update submit button and summary
        updateSubmitButton(allValid);
        updateSummary();
    });

    // Function to remove a file from the list
    function removeFile(index) {
        // Create new file list without the removed file
        const dt = new DataTransfer();
        selectedFiles.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        // Update the input and trigger change
        bookFileInput.files = dt.files;
        bookFileInput.dispatchEvent(new Event('change'));
    }

    // Function to show individual titles inputs
    function showIndividualTitles() {
        individualTitlesList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const titleInputWrapper = document.createElement('div');
            titleInputWrapper.className = 'flex items-center space-x-3';
            
            const fileName = document.createElement('span');
            fileName.className = 'text-sm text-gray-600 w-1/3 truncate';
            fileName.textContent = file.name;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `individual_titles[${index}]`;
            input.placeholder = `Title for ${file.name}`;
            input.className = 'flex-1 px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500';
            input.value = titleInput.value || ''; // Use default title as template
            
            titleInputWrapper.appendChild(fileName);
            titleInputWrapper.appendChild(input);
            individualTitlesList.appendChild(titleInputWrapper);
        });
        
        individualTitlesContainer.classList.remove('hidden');
    }

    // Function to hide individual titles inputs
    function hideIndividualTitles() {
        individualTitlesContainer.classList.add('hidden');
        individualTitlesList.innerHTML = '';
    }

    function updateSubmitButton(isValid) {
        if (isValid && selectedFiles.length > 0) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
        } else {
            submitBtn.disabled = selectedFiles.length === 0; // Allow submission without files (metadata only)
            if (submitBtn.disabled) {
                submitBtn.classList.add('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
            }
        }
    }

    // Handle book type selection and update summary
    function updateBookType() {
        if (paidBookRadio.checked) {
            // Show paid book UI
            priceSection.style.display = 'block';
            priceInput.required = true;
            
            // Update summary
            summaryType.textContent = 'Paid Books';
            summaryType.className = 'text-blue-600 font-semibold';
            summaryPriceLine.style.display = 'block';
            summaryAccess.textContent = 'Users who purchase the books';
            
            updatePriceDisplay();
        } else {
            // Show free book UI
            priceSection.style.display = 'none';
            priceInput.required = false;
            priceInput.value = '';
            
            // Update summary
            summaryType.textContent = 'Free Books';
            summaryType.className = 'text-green-600 font-semibold';
            summaryPriceLine.style.display = 'none';
            summaryAccess.textContent = 'All authenticated users';
        }
    }

    // Update price display in summary
    function updatePriceDisplay() {
        const price = parseFloat(priceInput.value) || 0;
        summaryPrice.textContent = 'KSh ' + price.toFixed(2);
    }

    // Update availability status in summary
    function updateAvailabilityStatus() {
        const isAvailable = document.getElementById('is_available').checked;
        summaryStatus.textContent = isAvailable ? 'Available immediately' : 'Saved as drafts';
        summaryStatus.className = isAvailable ? 'text-green-600 font-semibold' : 'text-yellow-600 font-semibold';
    }

    // Update upload summary
    function updateSummary() {
        summaryCount.textContent = selectedFiles.length;
        updateBookType();
        updateAvailabilityStatus();
    }

    // Initialize on page load
    updateSummary();

    // Add event listeners
    freeBookRadio.addEventListener('change', updateBookType);
    paidBookRadio.addEventListener('change', updateBookType);
    priceInput.addEventListener('input', updatePriceDisplay);
    document.getElementById('is_available').addEventListener('change', updateAvailabilityStatus);

    // Form validation and submission
    document.querySelector('form').addEventListener('submit', function(e) {
        // Validate paid book has a price
        if (paidBookRadio.checked && (!priceInput.value || parseFloat(priceInput.value) <= 0)) {
            e.preventDefault();
            alert('Please enter a valid price greater than 0 for paid books');
            priceInput.focus();
            return false;
        }

        // Show loading state and progress
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitLoader.classList.remove('hidden');
        
        // Show progress indicator
        const progressSection = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        
        progressSection.classList.remove('hidden');
        
        // Simulate progress (since we can't track real upload progress easily)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90; // Don't complete until actual submission
            
            progressBar.style.width = progress + '%';
            progressText.textContent = `Uploading ${selectedFiles.length} book(s)... ${Math.round(progress)}%`;
        }, 500);

        // Store interval ID to clear it if needed
        this.progressInterval = progressInterval;
    });

    // Re-enable submit button if user navigates back
    window.addEventListener('pageshow', function() {
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoader.classList.add('hidden');
        
        const progressSection = document.getElementById('upload-progress');
        progressSection.classList.add('hidden');
    });

    // Handle file drag and drop
    const dropZone = bookFileInput.parentElement;
    
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-maroon-500', 'bg-maroon-50');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-maroon-500', 'bg-maroon-50');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-maroon-500', 'bg-maroon-50');
        
        const files = Array.from(e.dataTransfer.files);
        const dt = new DataTransfer();
        
        // Filter for accepted file types
        const acceptedTypes = ['.pdf', '.epub', '.mobi', '.docx', '.txt'];
        const validFiles = files.filter(file => {
            const extension = '.' + file.name.split('.').pop().toLowerCase();
            return acceptedTypes.includes(extension);
        });
        
        validFiles.forEach(file => dt.items.add(file));
        bookFileInput.files = dt.files;
        bookFileInput.dispatchEvent(new Event('change'));
    });
});
</script>

@endsection