window.uploadManager = function () {
    return {
        files: [],
        previews: [],
        isDragging: false,
        analyzing: false,
        resultHtml: null,
        diagnosisId: null,

        init() {
            this.$nextTick(() => window.lucide && window.lucide.createIcons());
            window.uploadManagerContext = this;

            // Listen for locale change to re-translate AI result if present
            window.addEventListener('agriassist-locale-changed', (e) => {
                if (this.diagnosisId) {
                    this.refreshResult();
                }
            });
        },

        handleFileSelect(event) {
            this.addFiles(Array.from(event.target.files));
            event.target.value = '';
        },

        handleDrop(event) {
            this.isDragging = false;
            if (event.dataTransfer.files) {
                this.addFiles(Array.from(event.dataTransfer.files));
            }
        },

        addFiles(newFiles) {
            newFiles.forEach(file => {
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

        removeImage(index) {
            this.files.splice(index, 1);
            this.previews.splice(index, 1);
            this.$nextTick(() => window.lucide && window.lucide.createIcons());
        },

        resetForm() {
            this.files = [];
            this.previews = [];
            this.resultHtml = null;
            this.diagnosisId = null;
            const actualInput = document.getElementById('actual-input');
            if (actualInput) actualInput.value = '';
            this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        },

        refreshResult() {
            if (!this.diagnosisId) return;

            fetch(`/diagnosis/${this.diagnosisId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
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

        handleSubmit(event) {
            event.preventDefault();
            if (this.files.length === 0) return;

            this.analyzing = true;
            this.resultHtml = null;
            this.diagnosisId = null;

            const formData = new FormData(event.target);
            formData.delete('images[]');
            this.files.forEach(file => formData.append('images[]', file));

            fetch(event.target.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
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

window.resetForm = function () {
    if (window.uploadManagerContext) {
        window.uploadManagerContext.resetForm();
    }
};
