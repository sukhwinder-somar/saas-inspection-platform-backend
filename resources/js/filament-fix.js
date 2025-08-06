// Fix for Filament FileUpload in Builder blocks
document.addEventListener('DOMContentLoaded', function() {
    // Patch Filament's file upload component to prevent null reference errors
    function patchFileUploadComponent() {
        // Find and patch file upload elements that might cause errors
        const fileUploadElements = document.querySelectorAll('[x-data*="fileUpload"]');
        
        fileUploadElements.forEach(function(element) {
            if (element._x_dataStack && element._x_dataStack.length > 0) {
                const data = element._x_dataStack[0];
                
                // Add safe getFiles method if it doesn't exist or is problematic
                if (!data.getFiles || typeof data.getFiles !== 'function') {
                    data.getFiles = function() {
                        return this.files || [];
                    };
                }
                
                // Ensure files array exists
                if (!data.files) {
                    data.files = [];
                }
            }
        });
    }

    // Initial patch
    patchFileUploadComponent();
    
    // Watch for dynamically added Builder blocks and patch them
    const observer = new MutationObserver(function(mutations) {
        let shouldPatch = false;
        
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check if this is a file upload component or contains one
                    const hasFileUpload = node.matches && node.matches('[x-data*="fileUpload"]') ||
                                         node.querySelectorAll && node.querySelectorAll('[x-data*="fileUpload"]').length > 0;
                    
                    if (hasFileUpload) {
                        shouldPatch = true;
                    }
                }
            });
        });
        
        if (shouldPatch) {
            // Delay patching to allow Alpine to initialize
            setTimeout(patchFileUploadComponent, 100);
        }
    });

    // Start observing the entire document for Builder blocks
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Also patch on Alpine initialization
    document.addEventListener('alpine:initialized', function() {
        setTimeout(patchFileUploadComponent, 50);
    });
});