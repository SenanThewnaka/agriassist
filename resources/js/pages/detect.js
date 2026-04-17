/**
 * AgriAssist Image Upload & Analysis Manager
 * Handles multi-image selection, previews, drag-and-drop, and AI diagnosis submission.
 */
window.uploadManager = function () {
    return {
        files: [],
        previews: [],
        isDragging: false,
        analyzing: false,
        resultHtml: null,
        diagnosisId: null,

        /**
         * Initialize component and setup event listeners.
         */
        init() {
            this.$nextTick(() => window.lucide && window.lucide.createIcons());
            window.uploadManagerContext = this;

            // Handle locale changes to refresh the diagnosis display if active
            window.addEventListener('agriassist-locale-changed', (e) => {
                if (this.diagnosisId) {
                    this.refreshResult();
                }
            });
        },

        /**
         * Triggered when files are selected via the file input.
         */
        handleFileSelect(event) {
            this.addFiles(Array.from(event.target.files));
            event.target.value = '';
        },

        /**
         * Triggered when files are dropped into the drop zone.
         */
        handleDrop(event) {
            this.isDragging = false;
            if (event.dataTransfer.files) {
                this.addFiles(Array.from(event.dataTransfer.files));
            }
        },

        /**
         * Adds valid image files to the processing queue and generates previews.
         */
        addFiles(newFiles) {
            newFiles.forEach(file => {
                // Limit to 5 images per diagnosis for optimal AI accuracy
                if (this.files.length < 5 && file.type.startsWith('image/')) {
                    this.files.push(file);

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews.push(e.target.result);
                        this.$nextTick(() => window.lucide && window.lucide.createIcons());
                    };
                    reader.readAsDataURL(file);
                }
            });
        },

        /**
         * Removes a specific image from the preview list.
         */
        removeImage(index) {
            this.files.splice(index, 1);
            this.previews.splice(index, 1);
            this.$nextTick(() => window.lucide && window.lucide.createIcons());
        },

        /**
         * Resets the entire form to its initial state.
         */
        resetForm() {
            this.files = [];
            this.previews = [];
            this.resultHtml = null;
            this.diagnosisId = null;
            const actualInput = document.getElementById('actual-input');
            if (actualInput) actualInput.value = '';
            this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        },

        /**
         * Refetches the diagnosis result HTML, useful for language switching.
         */
        refreshResult() {
            if (!this.diagnosisId) return;

            fetch(`/diagnosis/${this.diagnosisId}`, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.html) {
                        this.resultHtml = data.html;
                        this.$nextTick(() => window.lucide && window.lucide.createIcons());
                    }
                })
                .catch(err => console.error('Failed to refresh diagnosis result:', err));
        },

        /**
         * Submits images to the backend for AI analysis.
         */
        handleSubmit(event) {
            event.preventDefault();
            if (this.files.length === 0) return;

            this.analyzing = true;
            this.resultHtml = null;
            this.diagnosisId = null;

            const formData = new FormData(event.target);
            // Replace the default file input field with our custom array
            formData.delete('images[]');
            this.files.forEach(file => formData.append('images[]', file));

            fetch(event.target.action, {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) return response.json().then(err => Promise.reject(err));
                    return response.json();
                })
                .then(data => {
                    this.analyzing = false;
                    if (data.html) {
                        this.diagnosisId = data.id;
                        this.resultHtml = data.html;
                        this.$nextTick(() => {
                            window.lucide && window.lucide.createIcons();
                            // Scroll to results after a short delay to ensure rendering
                            setTimeout(() => {
                                const resultElement = document.getElementById('analysis-result-container');
                                if (resultElement) {
                                    resultElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            }, 50);
                        });
                    }
                })
                .catch(error => {
                    this.analyzing = false;
                    const errorMsg = (window.__AGRI_CONFIG.detectTranslations && window.__AGRI_CONFIG.detectTranslations.analysisFailed)
                        || 'Analysis failed. Please try again.';
                    window.showToast(error.error || error.message || errorMsg, 'error');
                });
        }
    };
};

/**
 * Global helper to reset the upload form from outside Alpine context.
 */
window.resetForm = function () {
    if (window.uploadManagerContext) {
        window.uploadManagerContext.resetForm();
    }
};
