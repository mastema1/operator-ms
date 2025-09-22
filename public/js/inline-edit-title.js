/**
 * Inline Edit Title Component
 * 
 * Provides inline editing functionality for the dashboard title.
 * Users can click on the title to edit it in place and save changes.
 */

class InlineEditTitle {
    constructor(elementId, options = {}) {
        this.element = document.getElementById(elementId);
        this.originalText = '';
        this.isEditing = false;
        this.inputElement = null;
        
        // Configuration options
        this.options = {
            apiUrl: options.apiUrl || '/api/dashboard/title',
            maxLength: options.maxLength || 255,
            minLength: options.minLength || 1,
            placeholder: options.placeholder || 'Enter dashboard title...',
            confirmOnEnter: options.confirmOnEnter !== false,
            cancelOnEscape: options.cancelOnEscape !== false,
            showEditIcon: options.showEditIcon !== false,
            editIconClass: options.editIconClass || 'edit-icon',
            loadingClass: options.loadingClass || 'loading',
            errorClass: options.errorClass || 'error',
            successClass: options.successClass || 'success',
            ...options
        };
        
        this.init();
    }
    
    /**
     * Initialize the inline edit functionality
     */
    init() {
        if (!this.element) {
            console.error('InlineEditTitle: Element not found');
            return;
        }
        
        this.originalText = this.element.textContent.trim();
        this.setupElement();
        this.attachEventListeners();
        
        // Add edit icon if enabled
        if (this.options.showEditIcon) {
            this.addEditIcon();
        }
    }
    
    /**
     * Setup the element with necessary attributes and styling
     */
    setupElement() {
        this.element.style.cursor = 'pointer';
        this.element.setAttribute('title', 'Click to edit');
        this.element.classList.add('inline-editable');
        
        // Add some basic styling if not already present
        if (!this.element.style.padding) {
            this.element.style.padding = '4px 8px';
        }
        
        // Add hover effect
        this.element.addEventListener('mouseenter', () => {
            if (!this.isEditing) {
                this.element.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
                this.element.style.borderRadius = '4px';
            }
        });
        
        this.element.addEventListener('mouseleave', () => {
            if (!this.isEditing) {
                this.element.style.backgroundColor = '';
                this.element.style.borderRadius = '';
            }
        });
    }
    
    /**
     * Add edit icon next to the title
     */
    addEditIcon() {
        const icon = document.createElement('span');
        icon.className = this.options.editIconClass;
        icon.innerHTML = '✏️';
        icon.style.marginLeft = '8px';
        icon.style.fontSize = '0.8em';
        icon.style.opacity = '0.6';
        icon.style.cursor = 'pointer';
        icon.setAttribute('title', 'Click to edit');
        
        this.element.appendChild(icon);
    }
    
    /**
     * Attach event listeners
     */
    attachEventListeners() {
        this.element.addEventListener('click', (e) => {
            e.preventDefault();
            this.startEditing();
        });
        
        this.element.addEventListener('dblclick', (e) => {
            e.preventDefault();
            this.startEditing();
        });
    }
    
    /**
     * Start editing mode
     */
    startEditing() {
        if (this.isEditing) return;
        
        this.isEditing = true;
        this.originalText = this.element.textContent.trim();
        
        // Remove edit icon text from original text if present
        if (this.options.showEditIcon) {
            this.originalText = this.originalText.replace('✏️', '').trim();
        }
        
        this.createInputElement();
        this.replaceElementWithInput();
    }
    
    /**
     * Create the input element for editing
     */
    createInputElement() {
        this.inputElement = document.createElement('input');
        this.inputElement.type = 'text';
        this.inputElement.value = this.originalText;
        this.inputElement.maxLength = this.options.maxLength;
        this.inputElement.placeholder = this.options.placeholder;
        
        // Copy styling from original element
        const computedStyle = window.getComputedStyle(this.element);
        this.inputElement.style.fontSize = computedStyle.fontSize;
        this.inputElement.style.fontFamily = computedStyle.fontFamily;
        this.inputElement.style.fontWeight = computedStyle.fontWeight;
        this.inputElement.style.color = computedStyle.color;
        this.inputElement.style.backgroundColor = 'white';
        this.inputElement.style.border = '2px solid #007bff';
        this.inputElement.style.borderRadius = '4px';
        this.inputElement.style.padding = '4px 8px';
        this.inputElement.style.outline = 'none';
        this.inputElement.style.width = '100%';
        this.inputElement.style.minWidth = '200px';
        
        // Add event listeners to input
        this.inputElement.addEventListener('blur', () => this.saveChanges());
        this.inputElement.addEventListener('keydown', (e) => this.handleKeyDown(e));
        this.inputElement.addEventListener('input', (e) => this.handleInput(e));
    }
    
    /**
     * Replace the original element with the input
     */
    replaceElementWithInput() {
        // Create a wrapper to maintain layout
        const wrapper = document.createElement('div');
        wrapper.style.display = 'inline-block';
        wrapper.style.minWidth = this.element.offsetWidth + 'px';
        
        this.element.style.display = 'none';
        this.element.parentNode.insertBefore(wrapper, this.element);
        wrapper.appendChild(this.inputElement);
        
        // Focus and select the text
        this.inputElement.focus();
        this.inputElement.select();
    }
    
    /**
     * Handle keyboard input
     */
    handleKeyDown(e) {
        if (e.key === 'Enter' && this.options.confirmOnEnter) {
            e.preventDefault();
            this.saveChanges();
        } else if (e.key === 'Escape' && this.options.cancelOnEscape) {
            e.preventDefault();
            this.cancelEditing();
        }
    }
    
    /**
     * Handle input validation
     */
    handleInput(e) {
        const value = e.target.value;
        
        // Visual feedback for length limits
        if (value.length < this.options.minLength || value.length > this.options.maxLength) {
            this.inputElement.style.borderColor = '#dc3545';
        } else {
            this.inputElement.style.borderColor = '#007bff';
        }
    }
    
    /**
     * Save the changes
     */
    async saveChanges() {
        if (!this.isEditing) return;
        
        const newText = this.inputElement.value.trim();
        
        // Validate input
        if (newText.length < this.options.minLength) {
            this.showError(`Title must be at least ${this.options.minLength} character(s) long`);
            this.inputElement.focus();
            return;
        }
        
        if (newText.length > this.options.maxLength) {
            this.showError(`Title must be no more than ${this.options.maxLength} characters long`);
            this.inputElement.focus();
            return;
        }
        
        // If no changes, just cancel
        if (newText === this.originalText) {
            this.cancelEditing();
            return;
        }
        
        // Show loading state
        this.showLoading();
        
        try {
            const response = await this.saveToServer(newText);
            
            if (response.success) {
                this.originalText = newText;
                this.finishEditing(newText);
                this.showSuccess('Title updated successfully');
            } else {
                throw new Error(response.message || 'Failed to save title');
            }
        } catch (error) {
            console.error('Error saving title:', error);
            this.showError(error.message || 'Failed to save title');
            this.inputElement.focus();
        }
    }
    
    /**
     * Cancel editing and restore original text
     */
    cancelEditing() {
        if (!this.isEditing) return;
        
        this.finishEditing(this.originalText);
    }
    
    /**
     * Finish editing and restore the original element
     */
    finishEditing(text) {
        if (!this.isEditing) return;
        
        this.isEditing = false;
        
        // Update the original element
        this.element.textContent = text;
        
        // Add edit icon back if enabled
        if (this.options.showEditIcon) {
            this.addEditIcon();
        }
        
        // Remove the input and show the original element
        const wrapper = this.inputElement.parentNode;
        wrapper.parentNode.insertBefore(this.element, wrapper);
        wrapper.remove();
        
        this.element.style.display = '';
        this.clearMessages();
    }
    
    /**
     * Save the title to the server
     */
    async saveToServer(title) {
        const response = await fetch(this.options.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ title })
        });
        
        return await response.json();
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        this.inputElement.disabled = true;
        this.inputElement.style.opacity = '0.6';
        this.showMessage('Saving...', this.options.loadingClass);
    }
    
    /**
     * Show error message
     */
    showError(message) {
        this.inputElement.disabled = false;
        this.inputElement.style.opacity = '1';
        this.showMessage(message, this.options.errorClass);
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.showMessage(message, this.options.successClass);
        setTimeout(() => this.clearMessages(), 3000);
    }
    
    /**
     * Show a message near the element
     */
    showMessage(message, className) {
        this.clearMessages();
        
        const messageElement = document.createElement('div');
        messageElement.className = `inline-edit-message ${className}`;
        messageElement.textContent = message;
        messageElement.style.position = 'absolute';
        messageElement.style.backgroundColor = className === this.options.errorClass ? '#dc3545' : 
                                              className === this.options.successClass ? '#28a745' : '#6c757d';
        messageElement.style.color = 'white';
        messageElement.style.padding = '4px 8px';
        messageElement.style.borderRadius = '4px';
        messageElement.style.fontSize = '12px';
        messageElement.style.marginTop = '4px';
        messageElement.style.zIndex = '1000';
        messageElement.style.whiteSpace = 'nowrap';
        
        const wrapper = this.inputElement ? this.inputElement.parentNode : this.element.parentNode;
        wrapper.style.position = 'relative';
        wrapper.appendChild(messageElement);
    }
    
    /**
     * Clear all messages
     */
    clearMessages() {
        const messages = document.querySelectorAll('.inline-edit-message');
        messages.forEach(msg => msg.remove());
    }
    
    /**
     * Get the current title
     */
    getCurrentTitle() {
        return this.isEditing ? this.inputElement.value : this.element.textContent.trim();
    }
    
    /**
     * Set the title programmatically
     */
    setTitle(title) {
        if (this.isEditing) {
            this.inputElement.value = title;
        } else {
            this.element.textContent = title;
            if (this.options.showEditIcon) {
                this.addEditIcon();
            }
        }
        this.originalText = title;
    }
    
    /**
     * Destroy the inline edit functionality
     */
    destroy() {
        if (this.isEditing) {
            this.cancelEditing();
        }
        
        this.element.style.cursor = '';
        this.element.removeAttribute('title');
        this.element.classList.remove('inline-editable');
        this.clearMessages();
        
        // Remove event listeners (would need to store references to remove properly)
        // For now, just clear the element reference
        this.element = null;
    }
}

// Global function to initialize inline edit title
window.initInlineEditTitle = function(elementId, options = {}) {
    return new InlineEditTitle(elementId, options);
};

// Auto-initialize if element with data-inline-edit attribute exists
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('[data-inline-edit="title"]');
    elements.forEach(element => {
        const options = {
            apiUrl: element.dataset.apiUrl || '/api/dashboard/title',
            maxLength: parseInt(element.dataset.maxLength) || 255,
            minLength: parseInt(element.dataset.minLength) || 1,
            placeholder: element.dataset.placeholder || 'Enter dashboard title...',
            showEditIcon: element.dataset.showEditIcon !== 'false'
        };
        
        new InlineEditTitle(element.id, options);
    });
});
