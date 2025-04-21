// Function to load videos with timeout limit
function loadVideoWithTimeout(iframeElement, videoUrl, timeoutMs = 20000) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => {
        controller.abort();
        // Show error message
        Swal.fire({
            icon: 'error',
            title: 'Loading Error',
            text: 'The video took too long to load. Please try again later.',
            confirmButtonText: 'OK'
        });
    }, timeoutMs);

    fetch(videoUrl, { signal: controller.signal })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error loading video');
            }
            clearTimeout(timeoutId);
            iframeElement.src = videoUrl;
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                console.log('Video loading was cancelled due to timeout');
            } else {
                console.error('Error loading video:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while loading the video. Please try again.',
                    confirmButtonText: 'OK'
                });
            }
        });
}

// Function to initialize videos in modals
function initVideoModals() {
    const videoModals = document.querySelectorAll('[id$="VideoModal"]');
    videoModals.forEach(modal => {
        const iframe = modal.querySelector('iframe');
        if (iframe) {
            const originalSrc = iframe.getAttribute('src');
            
            modal.addEventListener('show.bs.modal', () => {
                if (originalSrc) {
                    loadVideoWithTimeout(iframe, originalSrc);
                }
            });

            modal.addEventListener('hide.bs.modal', () => {
                iframe.removeAttribute('src');
            });
        }
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initVideoModals);