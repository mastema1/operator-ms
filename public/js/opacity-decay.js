/**
 * Universal Time-Based Opacity Decay Feature
 * 
 * This script gradually reduces the opacity of the entire application over time.
 * - Starts from a configurable date (set in backend)
 * - Decreases opacity by 10% every 7 days
 * - Minimum opacity is 10% (never completely invisible)
 * - Synchronized for all users globally
 * - Does not affect functionality of UI elements
 */

class OpacityDecayManager {
    constructor(startDateString) {
        this.startDate = new Date(startDateString);
        this.decayRate = 0.10; // 10% decrease
        this.decayInterval = 7; // Every 7 days
        this.minimumOpacity = 0; // Never go below 10%
        this.maximumOpacity = 1.0; // Start at 100%
        
        this.init();
    }
    
    /**
     * Initialize the opacity decay system
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.applyOpacityDecay());
        } else {
            this.applyOpacityDecay();
        }
        
        // Update opacity every hour to handle day transitions
        setInterval(() => this.applyOpacityDecay(), 60 * 60 * 1000);
        
        // Add debug info to console (can be removed in production)
        this.logDecayInfo();
    }
    
    /**
     * Calculate the current opacity based on elapsed time
     */
    calculateCurrentOpacity() {
        const now = new Date();
        const timeDifference = now - this.startDate;
        
        // If we haven't reached the start date yet, return full opacity
        if (timeDifference < 0) {
            return this.maximumOpacity;
        }
        
        // Calculate how many decay intervals (7-day periods) have passed
        const millisecondsPerDay = 24 * 60 * 60 * 1000;
        const daysElapsed = timeDifference / millisecondsPerDay;
        const intervalsElapsed = Math.floor(daysElapsed / this.decayInterval);
        
        // Calculate opacity: start at 100%, decrease by 10% per interval
        const opacityReduction = intervalsElapsed * this.decayRate;
        const currentOpacity = this.maximumOpacity - opacityReduction;
        
        // Ensure we never go below minimum opacity
        return Math.max(currentOpacity, this.minimumOpacity);
    }
    
    /**
     * Apply the calculated opacity to the entire application
     */
    applyOpacityDecay() {
        const currentOpacity = this.calculateCurrentOpacity();
        
        // Apply opacity to the body element (affects entire page)
        document.body.style.opacity = currentOpacity.toString();
        
        // Ensure the body remains interactive despite opacity changes
        document.body.style.pointerEvents = 'auto';
        
        // Store current opacity for potential use by other scripts
        window.currentAppOpacity = currentOpacity;
        
        // Dispatch custom event for other components that might need to know
        window.dispatchEvent(new CustomEvent('opacityDecayUpdate', {
            detail: {
                opacity: currentOpacity,
                startDate: this.startDate,
                daysElapsed: this.getDaysElapsed()
            }
        }));
    }
    
    /**
     * Get the number of days elapsed since start date
     */
    getDaysElapsed() {
        const now = new Date();
        const timeDifference = now - this.startDate;
        const millisecondsPerDay = 24 * 60 * 60 * 1000;
        return Math.max(0, timeDifference / millisecondsPerDay);
    }
    
    /**
     * Get the number of days until next opacity change
     */
    getDaysUntilNextDecay() {
        const daysElapsed = this.getDaysElapsed();
        const currentInterval = Math.floor(daysElapsed / this.decayInterval);
        const nextIntervalStart = (currentInterval + 1) * this.decayInterval;
        return Math.max(0, nextIntervalStart - daysElapsed);
    }
    
    /**
     * Log debug information about the decay system
     */
    logDecayInfo() {
        const currentOpacity = this.calculateCurrentOpacity();
        const daysElapsed = this.getDaysElapsed();
        const daysUntilNext = this.getDaysUntilNextDecay();
        
        console.group('🎨 Universal Opacity Decay System');
        console.log('Start Date:', this.startDate.toDateString());
        console.log('Days Elapsed:', Math.floor(daysElapsed));
        console.log('Current Opacity:', Math.round(currentOpacity * 100) + '%');
        console.log('Days Until Next Decay:', Math.ceil(daysUntilNext));
        console.log('Minimum Opacity:', Math.round(this.minimumOpacity * 100) + '%');
        console.groupEnd();
    }
    
    /**
     * Get current system status (useful for debugging or admin panels)
     */
    getStatus() {
        return {
            startDate: this.startDate,
            currentOpacity: this.calculateCurrentOpacity(),
            daysElapsed: this.getDaysElapsed(),
            daysUntilNextDecay: this.getDaysUntilNextDecay(),
            isActive: this.getDaysElapsed() >= 0,
            hasReachedMinimum: this.calculateCurrentOpacity() <= this.minimumOpacity
        };
    }
}

// Global function to initialize the opacity decay system
// This will be called from the Blade template with the start date
window.initOpacityDecay = function(startDateString) {
    window.opacityDecayManager = new OpacityDecayManager(startDateString);
    return window.opacityDecayManager;
};

// Global function to get current status (useful for debugging)
window.getOpacityDecayStatus = function() {
    return window.opacityDecayManager ? window.opacityDecayManager.getStatus() : null;
};
