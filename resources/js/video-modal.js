// Video Modal Functionality
window.currentVideoElement = null;

function openVideoModal(embedUrl, title, isLocal = false) {
    console.log('Opening video modal:', { embedUrl, title, isLocal });
    
    const modal = document.getElementById('videoModal');
    const videoTitle = document.getElementById('videoTitle');
    const videoContainer = document.getElementById('videoContainer');
    const videoLoader = document.getElementById('videoLoader');
    
    if (!modal || !videoTitle || !videoContainer) {
        console.error('Modal elements not found');
        alert('Video player is not available. Please refresh the page.');
        return;
    }

    // Set title
    videoTitle.textContent = title;
    
    // Clear previous content and show loader
    videoContainer.innerHTML = '<div id="videoLoader" class="absolute inset-0 flex items-center justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div></div>';
    
    // Show modal first
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    try {
        setTimeout(() => {
            if (isLocal) {
                createLocalVideoPlayer(embedUrl, videoContainer);
            } else {
                createExternalVideoPlayer(embedUrl, videoContainer);
            }
        }, 100);
        
    } catch (error) {
        console.error('Error opening video modal:', error);
        showVideoError('Error loading video player');
    }
}

// Expose openVideoModal globally
window.openVideoModal = openVideoModal;

function createLocalVideoPlayer(embedUrl, container) {
    console.log('Creating local video player for:', embedUrl);
    
    const video = document.createElement('video');
    video.className = 'w-full h-full';
    video.controls = true;
    video.preload = 'metadata';
    video.poster = '';
    
    // Add comprehensive event listeners
    video.addEventListener('loadstart', () => {
        console.log('Video: Load start');
        hideVideoLoader();
    });
    
    video.addEventListener('loadedmetadata', () => {
        console.log('Video: Metadata loaded - Duration:', video.duration);
    });
    
    video.addEventListener('loadeddata', () => {
        console.log('Video: Data loaded');
    });
    
    video.addEventListener('canplay', () => {
        console.log('Video: Can play');
        hideVideoLoader();
    });
    
    video.addEventListener('error', (e) => {
        console.error('Video error:', e);
        console.error('Video error details:', video.error);
        let errorMessage = 'Failed to load video.';
        
        if (video.error) {
            switch (video.error.code) {
                case video.error.MEDIA_ERR_ABORTED:
                    errorMessage = 'Video loading was aborted.';
                    break;
                case video.error.MEDIA_ERR_NETWORK:
                    errorMessage = 'Network error while loading video.';
                    break;
                case video.error.MEDIA_ERR_DECODE:
                    errorMessage = 'Video file is corrupted or unsupported format.';
                    break;
                case video.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                    errorMessage = 'Video format not supported by your browser.';
                    break;
            }
        }
        
        showVideoError(errorMessage);
    });
    
    video.addEventListener('stalled', () => {
        console.warn('Video: Network stalled');
    });
    
    video.addEventListener('waiting', () => {
        console.log('Video: Waiting for data');
    });

    // Create source element
    const source = document.createElement('source');
    source.src = embedUrl;
    
    // Determine MIME type from file extension
    const extension = embedUrl.toLowerCase().split('.').pop().split('?')[0];
    switch (extension) {
        case 'mp4':
            source.type = 'video/mp4';
            break;
        case 'webm':
            source.type = 'video/webm';
            break;
        case 'ogg':
            source.type = 'video/ogg';
            break;
        case 'mov':
            source.type = 'video/quicktime';
            break;
        case 'avi':
            source.type = 'video/x-msvideo';
            break;
        default:
            source.type = 'video/mp4'; // Default fallback
    }
    
    console.log('Video source type:', source.type);
    
    video.appendChild(source);
    
    // Add fallback text
    video.innerHTML += '<p class="text-white p-4">Your browser does not support the video tag or this video format.</p>';
    
    window.currentVideoElement = video;
    container.appendChild(video);
}

function createExternalVideoPlayer(embedUrl, container) {
    console.log('Creating external video player for:', embedUrl);
    
    let iframeUrl = embedUrl;
    
    // Process different video platform URLs
    if (embedUrl.includes('youtube.com/watch')) {
        const videoId = embedUrl.split('v=')[1]?.split('&')[0];
        if (videoId) {
            iframeUrl = `https://www.youtube.com/embed/${videoId}?autoplay=0&rel=0`;
            console.log('Converted YouTube URL:', iframeUrl);
        }
    } else if (embedUrl.includes('youtu.be/')) {
        const videoId = embedUrl.split('youtu.be/')[1]?.split('?')[0];
        if (videoId) {
            iframeUrl = `https://www.youtube.com/embed/${videoId}?autoplay=0&rel=0`;
            console.log('Converted YouTube short URL:', iframeUrl);
        }
    } else if (embedUrl.includes('vimeo.com/') && !embedUrl.includes('/embed/')) {
        const videoId = embedUrl.split('vimeo.com/')[1]?.split('/')[0];
        if (videoId) {
            iframeUrl = `https://player.vimeo.com/video/${videoId}?autoplay=0`;
            console.log('Converted Vimeo URL:', iframeUrl);
        }
    }
    
    const iframe = document.createElement('iframe');
    iframe.className = 'w-full h-full';
    iframe.src = iframeUrl;
    iframe.frameBorder = '0';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    
    iframe.addEventListener('load', () => {
        console.log('Iframe loaded successfully');
        hideVideoLoader();
    });
    
    iframe.addEventListener('error', () => {
        console.error('Iframe failed to load');
        showVideoError('External video failed to load.');
    });
    
    window.currentVideoElement = iframe;
    container.appendChild(iframe);
    
    // Hide loader after a short delay for iframes
    setTimeout(hideVideoLoader, 1000);
}

function hideVideoLoader() {
    const loader = document.getElementById('videoLoader');
    if (loader) {
        loader.remove();
    }
}

function showVideoError(message) {
    const videoContainer = document.getElementById('videoContainer');
    videoContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-white bg-red-600 p-8">
            <svg class="w-16 h-16 mb-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <p class="text-center text-lg mb-2">Oops! Something went wrong</p>
            <p class="text-center text-sm opacity-90">${message}</p>
            <button onclick="closeVideoModal()" class="mt-4 bg-white text-red-600 px-4 py-2 rounded hover:bg-gray-100">
                Close
            </button>
        </div>
    `;
}

// Expose functions globally
window.createLocalVideoPlayer = createLocalVideoPlayer;
window.createExternalVideoPlayer = createExternalVideoPlayer;
window.hideVideoLoader = hideVideoLoader;
window.showVideoError = showVideoError;

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const videoContainer = document.getElementById('videoContainer');
    
    // Stop any playing video
    if (window.currentVideoElement) {
        if (window.currentVideoElement.tagName === 'VIDEO') {
            window.currentVideoElement.pause();
            window.currentVideoElement.src = '';
        }
        window.currentVideoElement = null;
    }
    
    // Clear container
    videoContainer.innerHTML = '';
    
    // Hide modal
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    console.log('Video modal closed');
}

// Expose closeVideoModal globally
window.closeVideoModal = closeVideoModal;

// Event listeners for modal closing
document.addEventListener('click', function(e) {
    const modal = document.getElementById('videoModal');
    if (e.target === modal) {
        closeVideoModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});
