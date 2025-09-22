{{-- Modal for Watching Videos --}}
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-4xl max-h-screen overflow-auto relative">
        <button 
            onclick="closeVideoModal()" 
            class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 z-10 bg-white rounded-full p-2">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        
        <div class="p-6">
            <h2 id="videoModalTitle" class="text-2xl font-bold mb-4"></h2>
            
            {{-- For embedded videos (YouTube, Vimeo, etc.) --}}
            <div id="iframeWrapper" class="hidden">
                <div class="relative w-full" style="padding-bottom: 56.25%; height: 0;">
                    <iframe 
                        id="videoModalIframe" 
                        src="" 
                        frameborder="0" 
                        allow="autoplay; encrypted-media" 
                        allowfullscreen 
                        class="absolute top-0 left-0 w-full h-full rounded">
                    </iframe>
                </div>
            </div>
            
            {{-- For local MP4 videos --}}
            <div id="videoWrapper" class="hidden">
                <video 
                    id="videoModalPlayer" 
                    controls 
                    preload="metadata"
                    class="w-full max-h-96 rounded"
                    style="max-height: 70vh;">
                    <source id="videoSource" src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>
</div>

<script>
    function openVideoModal(embedUrl, title, isLocal = false) {
        console.group('Video Modal Debug');
        console.log('Embed URL:', embedUrl);
        console.log('Title:', title);
        console.log('Is Local:', isLocal);

        const modal = document.getElementById('videoModal');
        const iframeWrapper = document.getElementById('iframeWrapper');
        const videoWrapper = document.getElementById('videoWrapper');
        const iframe = document.getElementById('videoModalIframe');
        const videoPlayer = document.getElementById('videoModalPlayer');
        const videoSource = document.getElementById('videoSource');
        const titleElement = document.getElementById('videoModalTitle');

        titleElement.textContent = title;

        // Reset both players
        iframe.src = '';
        videoPlayer.pause();
        videoPlayer.currentTime = 0;
        videoSource.src = '';

        // Validate URL
        if (!embedUrl) {
            console.error('No video URL provided');
            alert('No video URL found. Please contact support.');
            console.groupEnd();
            return;
        }

        // Ensure full URL for local videos
        if (isLocal && !embedUrl.startsWith('http')) {
            embedUrl = window.location.origin + '/storage/' + embedUrl;
            console.log('Corrected Local Video URL:', embedUrl);
        }

        try {
            if (isLocal) {
                console.log('Loading local video:', embedUrl);
                iframeWrapper.classList.add('hidden');
                videoWrapper.classList.remove('hidden');
                videoSource.src = embedUrl;
                videoPlayer.load();
                
                // Add comprehensive error handling for video
                videoPlayer.onerror = function(e) {
                    console.error('Video Error:', e);
                    console.error('Error loading video:', {
                        src: videoSource.src,
                        networkState: videoPlayer.networkState,
                        readyState: videoPlayer.readyState,
                        error: videoPlayer.error
                    });
                    alert('Error loading video. Please check the video file path and permissions.');
                };
                
                videoPlayer.onloadeddata = function() {
                    console.log('Video loaded successfully');
                    console.log('Video details:', {
                        duration: videoPlayer.duration,
                        videoWidth: videoPlayer.videoWidth,
                        videoHeight: videoPlayer.videoHeight
                    });
                };
                
            } else {
                console.log('Loading embedded video:', embedUrl);
                videoWrapper.classList.add('hidden');
                iframeWrapper.classList.remove('hidden');
                iframe.src = embedUrl;
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        } catch (error) {
            console.error('Unexpected error in openVideoModal:', error);
            alert('An unexpected error occurred while trying to play the video.');
        }

        console.groupEnd();
    }

    function closeVideoModal() {
        const modal = document.getElementById('videoModal');
        const iframe = document.getElementById('videoModalIframe');
        const videoPlayer = document.getElementById('videoModalPlayer');

        iframe.src = '';
        videoPlayer.pause();
        videoPlayer.currentTime = 0;

        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Close modal when clicking outside
    document.getElementById('videoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeVideoModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVideoModal();
        }
    });
</script>

