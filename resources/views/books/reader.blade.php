@extends('layouts.app')

@section('title', 'Reading: ' . $book->title)

@section('content')
<div class="flex flex-col h-screen">
    <!-- Header Bar -->
    <div class="bg-white border-b px-4 py-3 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('books') }}" 
                   class="text-gray-600 hover:text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>
                <div class="border-l pl-4">
                    <h1 class="text-lg font-semibold text-gray-800">{{ $book->title }}</h1>
                    <p class="text-sm text-gray-600">{{ $book->getFileTypeNameAttribute() }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Viewer Controls -->
                <div id="viewer-controls" class="flex items-center space-x-2" style="display: none;">
                    <button onclick="previousPage()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                        ← Prev
                    </button>
                    <span id="page-info" class="text-sm text-gray-600">Page 1 of ?</span>
                    <button onclick="nextPage()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                        Next →
                    </button>
                    <button onclick="zoomOut()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded text-sm">
                        -
                    </button>
                    <span id="zoom-info" class="text-sm text-gray-600">100%</span>
                    <button onclick="zoomIn()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded text-sm">
                        +
                    </button>
                </div>
                
                <!-- Download Button -->
                @auth
                    @php
                        $canDownload = \App\Models\Transaction::where('user_id', auth()->id())
                            ->where('content_type', 'book')
                            ->where('content_id', $book->id)
                            ->where('status', 'paid')
                            ->exists();
                    @endphp
                    
                    @if($canDownload)
                        <a href="{{ route('books.download', $book) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                            Download
                        </a>
                    @else
                        <a href="{{ route('mpesa.payment.book', $book) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                            Buy (KSh {{ number_format($book->price) }})
                        </a>
                    @endif
                @endauth
                
                <!-- Viewer Options -->
                <div class="relative">
                    <button onclick="toggleViewerMenu()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                        ⚙️ Options
                    </button>
                    <div id="viewer-menu" class="absolute right-0 top-full mt-1 bg-white border rounded shadow-lg z-20 hidden">
                        <button onclick="switchToGoogleViewer()" 
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Google Docs Viewer
                        </button>
                        <button onclick="switchToOfficeViewer()" 
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Microsoft Office Viewer
                        </button>
                        <button onclick="openInNewTab()" 
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                            Open in New Tab
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer -->
    <div class="flex-1 relative bg-gray-100">
        <!-- Loading State -->
        <div id="loading-screen" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-10">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Loading document...</p>
                <p class="text-sm text-gray-500">Preparing viewer...</p>
            </div>
        </div>

        <!-- PDF Viewer (Primary) -->
        <div id="pdf-viewer" class="w-full h-full" style="display: none;">
            <div id="pdf-container" class="w-full h-full flex justify-center items-start overflow-auto p-4">
                <canvas id="pdf-canvas" class="shadow-lg bg-white"></canvas>
            </div>
        </div>

        <!-- Iframe Fallback -->
        <div id="iframe-viewer" class="w-full h-full" style="display: none;">
            <iframe id="document-frame" 
                    class="w-full h-full border-none"
                    allow="fullscreen"
                    referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- Error State -->
        <div id="error-screen" class="absolute inset-0 flex items-center justify-center bg-gray-50" style="display: none;">
            <div class="text-center max-w-md">
                <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cannot Display Document</h3>
                <p class="text-gray-600 mb-4">The document format is not supported for embedded viewing.</p>
                <a href="{{ $file_url }}" target="_blank" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-block">
                    Open in New Tab
                </a>
            </div>
        </div>
    </div>
</div>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// Configuration
const fileUrl = '{{ $file_url }}';
const fileType = '{{ $book->getFileType() }}';
const bookTitle = '{{ $book->title }}';

// PDF.js setup
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// PDF viewer state
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let scale = 1.2;

// Initialize viewer based on file type
async function initializeViewer() {
    console.log('Initializing viewer for file type:', fileType);
    
    try {
        if (fileType === 'pdf') {
            await loadPDFViewer();
        } else {
            loadIframeViewer();
        }
    } catch (error) {
        console.error('Viewer initialization failed:', error);
        showError();
    }
}

// PDF Viewer Implementation
async function loadPDFViewer() {
    try {
        console.log('Loading PDF with PDF.js:', fileUrl);
        
        const loadingTask = pdfjsLib.getDocument({
            url: fileUrl,
            cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
            cMapPacked: true,
        });

        pdfDoc = await loadingTask.promise;
        console.log('PDF loaded successfully. Pages:', pdfDoc.numPages);
        
        // Show PDF viewer
        document.getElementById('loading-screen').style.display = 'none';
        document.getElementById('pdf-viewer').style.display = 'block';
        document.getElementById('viewer-controls').style.display = 'flex';
        
        // Render first page
        renderPage(pageNum);
        
    } catch (error) {
        console.error('PDF.js failed:', error);
        // Fallback to iframe
        loadIframeViewer();
    }
}

async function renderPage(num) {
    pageRendering = true;
    
    try {
        const page = await pdfDoc.getPage(num);
        const viewport = page.getViewport({ scale: scale });
        
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        
        await page.render(renderContext).promise;
        
        // Update UI
        document.getElementById('page-info').textContent = `Page ${num} of ${pdfDoc.numPages}`;
        document.getElementById('zoom-info').textContent = `${Math.round(scale * 100)}%`;
        
        pageRendering = false;
        
        if (pageNumPending !== null) {
            renderPage(pageNumPending);
            pageNumPending = null;
        }
        
    } catch (error) {
        console.error('Error rendering page:', error);
        pageRendering = false;
    }
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

// Navigation functions
function previousPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

function nextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

function zoomIn() {
    scale *= 1.2;
    queueRenderPage(pageNum);
}

function zoomOut() {
    scale /= 1.2;
    queueRenderPage(pageNum);
}

// Iframe Viewer Implementation
function loadIframeViewer() {
    console.log('Loading iframe viewer for:', fileType);
    
    const iframe = document.getElementById('document-frame');
    let viewerUrls = [];
    
    switch (fileType) {
        case 'pdf':
            viewerUrls = [
                `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`,
                `https://mozilla.github.io/pdf.js/web/viewer.html?file=${encodeURIComponent(fileUrl)}`
            ];
            break;
        case 'ppt':
            viewerUrls = [
                `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`,
                `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`,
                fileUrl // Direct file as last resort
            ];
            break;
        case 'doc':
            viewerUrls = [
                `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`,
                `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`
            ];
            break;
        default:
            viewerUrls = [
                `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`,
                fileUrl
            ];
    }
    
    tryViewerUrl(viewerUrls, 0);
}

function tryViewerUrl(urls, index) {
    if (index >= urls.length) {
        console.error('All viewer URLs failed');
        showError();
        return;
    }
    
    const iframe = document.getElementById('document-frame');
    const currentUrl = urls[index];
    
    console.log(`Trying viewer ${index + 1}/${urls.length}:`, currentUrl);
    
    iframe.onload = function() {
        console.log(`Viewer ${index + 1} loaded successfully`);
        document.getElementById('loading-screen').style.display = 'none';
        document.getElementById('iframe-viewer').style.display = 'block';
    };
    
    iframe.onerror = function() {
        console.log(`Viewer ${index + 1} failed, trying next...`);
        tryViewerUrl(urls, index + 1);
    };
    
    // Set timeout for each attempt
    const timeout = setTimeout(() => {
        if (document.getElementById('loading-screen').style.display !== 'none') {
            console.log(`Viewer ${index + 1} timed out, trying next...`);
            tryViewerUrl(urls, index + 1);
        }
    }, 8000); // 8 seconds per attempt
    
    iframe.src = currentUrl;
}

// Viewer option functions
function toggleViewerMenu() {
    const menu = document.getElementById('viewer-menu');
    menu.classList.toggle('hidden');
}

function switchToGoogleViewer() {
    const iframe = document.getElementById('document-frame');
    const googleUrl = `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;
    
    document.getElementById('loading-screen').style.display = 'flex';
    document.getElementById('iframe-viewer').style.display = 'none';
    document.getElementById('viewer-menu').classList.add('hidden');
    
    iframe.src = googleUrl;
    iframe.onload = function() {
        document.getElementById('loading-screen').style.display = 'none';
        document.getElementById('iframe-viewer').style.display = 'block';
    };
}

function switchToOfficeViewer() {
    const iframe = document.getElementById('document-frame');
    const officeUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
    
    document.getElementById('loading-screen').style.display = 'flex';
    document.getElementById('iframe-viewer').style.display = 'none';
    document.getElementById('viewer-menu').classList.add('hidden');
    
    iframe.src = officeUrl;
    iframe.onload = function() {
        document.getElementById('loading-screen').style.display = 'none';
        document.getElementById('iframe-viewer').style.display = 'block';
    };
}

function openInNewTab() {
    window.open(fileUrl, '_blank');
    document.getElementById('viewer-menu').classList.add('hidden');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('viewer-menu');
    const button = event.target.closest('button');
    
    if (!menu.contains(event.target) && button?.textContent !== '⚙️ Options') {
        menu.classList.add('hidden');
    }
});

function showError() {
    document.getElementById('loading-screen').style.display = 'none';
    document.getElementById('pdf-viewer').style.display = 'none';
    document.getElementById('iframe-viewer').style.display = 'none';
    document.getElementById('error-screen').style.display = 'flex';
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (pdfDoc && document.getElementById('pdf-viewer').style.display !== 'none') {
        if (e.key === 'ArrowLeft') previousPage();
        if (e.key === 'ArrowRight') nextPage();
        if (e.key === '+' || e.key === '=') zoomIn();
        if (e.key === '-') zoomOut();
    }
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeViewer();
});

// Log access for analytics
console.log('Document reader initialized:', {
    book_id: {{ $book->id }},
    title: bookTitle,
    file_url: fileUrl,
    file_type: fileType
});
</script>

<style>
/* Spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Ensure full height layout */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Canvas styling */
#pdf-canvas {
    max-width: 100%;
    height: auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #viewer-controls {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    #viewer-controls button {
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .flex.items-center.justify-between {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
@endsection