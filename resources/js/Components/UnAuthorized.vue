<template>
  <div class="error-container">
    <div class="error-content" :class="{ 'shake': isShaking }">
      <!-- Animated Lock Icon -->
      <div class="lock-container" @click="shakeLock">
        <div class="lock-body" :style="{ transform: `rotate(${lockRotation}deg)` }">
          <div class="lock-shackle" :class="{ 'wiggle': isWiggling }"></div>
          <div class="lock-face">
            <div class="keyhole" :class="{ 'glow': isGlowing }"></div>
          </div>
        </div>
      </div>

      <!-- Error Code with Hover Effect -->
      <h1 class="error-code" @mouseenter="startGlow" @mouseleave="stopGlow">
        4<span class="zero" :class="{ 'spin': isSpinning }" @click="spinZero">0</span>3
      </h1>

      <!-- Dynamic Message -->
      <h2 class="error-title">{{ currentTitle }}</h2>
      <p class="error-message">{{ currentMessage }}</p>

      <!-- Interactive Buttons -->
      <!-- <div class="button-group">
        <button 
          class="btn btn-primary" 
          @click="goHome"
          @mouseenter="buttonHover = 'home'"
          @mouseleave="buttonHover = null"
          :class="{ 'pulse': buttonHover === 'home' }"
        >
          <span class="icon">🏠</span>
          Take Me Home
        </button>
        
        <button 
          class="btn btn-secondary" 
          @click="tryAgain"
          @mouseenter="buttonHover = 'retry'"
          @mouseleave="buttonHover = null"
          :class="{ 'pulse': buttonHover === 'retry' }"
        >
          <span class="icon">🔄</span>
          Try Again
        </button>
        
        <button 
          class="btn btn-outline" 
          @click="contactSupport"
          @mouseenter="buttonHover = 'support'"
          @mouseleave="buttonHover = null"
          :class="{ 'pulse': buttonHover === 'support' }"
        >
          <span class="icon">💬</span>
          Contact Support
        </button>
      </div> -->

      <!-- Fun Facts Section -->
      <!-- <div class="fun-section" v-if="showFacts">
        <p class="fun-fact" @click="generateNewFact">
          <span class="fact-icon">💡</span>
          {{ currentFact }}
          <span class="click-hint">(click for another fact)</span>
        </p>
      </div> -->

      <!-- Toggle Fun Facts -->
      <!-- <button class="toggle-facts" @click="toggleFacts">
        {{ showFacts ? 'Hide' : 'Show' }} Fun Facts
      </button> -->
    </div>

    <!-- Floating particles for visual interest -->
    <div class="particles">
      <div v-for="n in particleCount" :key="n" class="particle" :style="getParticleStyle(n)"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Error403Component',
  data() {
    return {
      isShaking: false,
      isWiggling: false,
      isGlowing: false,
      isSpinning: false,
      lockRotation: 0,
      buttonHover: null,
      showFacts: false,
      currentFactIndex: 0,
      particleCount: 20,
      
      titles: [
        'Access Denied',
        // 'You Shall Not Pass!',
        // 'Permission Denied',
        // 'Restricted Area'
      ],
      
      messages: [
        // "Sorry, you don't have permission to access this resource.",
        // "This area is off-limits. Please check your credentials.",
        "You don't have permission to access this resource.",
        // "Forbidden zone detected. Authorization required."
      ],
      
      funFacts: [
        "HTTP 403 was first defined in 1992!",
        "403 errors protect millions of websites daily.",
        "The lock icon has been a security symbol since ancient times.",
        "Error codes help developers debug issues faster.",
        "403 means 'understood but refused' in HTTP language.",
        "Security through obscurity isn't real security!",
        "Authentication ≠ Authorization (that's why 403 exists!)"
      ]
    }
  },
  
  computed: {
    currentTitle() {
      return this.titles[Math.floor(Date.now() / 3000) % this.titles.length];
    },
    
    currentMessage() {
      return this.messages[Math.floor(Date.now() / 3000) % this.messages.length];
    },
    
    currentFact() {
      return this.funFacts[this.currentFactIndex];
    }
  },
  
  methods: {
    shakeLock() {
      this.isShaking = true;
      this.isWiggling = true;
      this.lockRotation = Math.random() * 20 - 10;
      
      setTimeout(() => {
        this.isShaking = false;
        this.isWiggling = false;
        this.lockRotation = 0;
      }, 600);
    },
    
    startGlow() {
      this.isGlowing = true;
    },
    
    stopGlow() {
      this.isGlowing = false;
    },
    
    spinZero() {
      this.isSpinning = true;
      setTimeout(() => {
        this.isSpinning = false;
      }, 1000);
    },
    
    goHome() {
      // Emit event or use router
      this.$emit('go-home');
      console.log('Navigating to home...');
    },
    
    tryAgain() {
      // Refresh or retry logic
      this.$emit('try-again');
      console.log('Trying again...');
      this.shakeLock();
    },
    
    contactSupport() {
      // Open support modal or navigate
      this.$emit('contact-support');
      console.log('Opening support...');
    },
    
    toggleFacts() {
      this.showFacts = !this.showFacts;
    },
    
    generateNewFact() {
      this.currentFactIndex = (this.currentFactIndex + 1) % this.funFacts.length;
    },
    
    getParticleStyle(index) {
      const delay = Math.random() * 5;
      const duration = 3 + Math.random() * 4;
      const size = 2 + Math.random() * 4;
      
      return {
        left: Math.random() * 100 + '%',
        animationDelay: delay + 's',
        animationDuration: duration + 's',
        width: size + 'px',
        height: size + 'px'
      };
    }
  }
}
</script>

<style scoped>
.error-container {
  min-height: 86vh;
  background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  position: relative;
  overflow: hidden;
}

.error-content {
  text-align: center;
  max-width: 600px;
  padding: 2rem;
  position: relative;
  z-index: 2;
}

.shake {
  animation: shakeAnimation 0.6s ease-in-out;
}

/* Lock Animation */
.lock-container {
  margin-bottom: 2rem;
  cursor: pointer;
  display: inline-block;
  transition: transform 0.2s ease;
}

.lock-container:hover {
  transform: scale(1.1);
}

.lock-body {
  width: 80px;
  height: 60px;
  position: relative;
  margin: 0 auto;
  transition: transform 0.3s ease;
}

.lock-shackle {
  width: 40px;
  height: 40px;
  border: 6px solid #6366f1;
  border-bottom: none;
  border-radius: 40px 40px 0 0;
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  transition: all 0.3s ease;
}

.lock-shackle.wiggle {
  animation: wiggle 0.6s ease-in-out;
}

.lock-face {
  width: 60px;
  height: 45px;
  background: #6366f1;
  border-radius: 8px;
  position: relative;
  margin: 0 auto;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
}

.keyhole {
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.3s ease;
}

.keyhole::after {
  content: '';
  width: 2px;
  height: 8px;
  background: white;
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
}

.keyhole.glow {
  box-shadow: 0 0 10px #fbbf24, 0 0 20px #fbbf24;
  background: #fbbf24;
}

/* Error Code Styling */
.error-code {
  font-size: 8rem;
  font-weight: 800;
  color: #6366f1;
  margin: 1rem 0;
  text-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
  transition: all 0.3s ease;
  user-select: none;
}

.error-code:hover {
  text-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
}

.zero {
  display: inline-block;
  cursor: pointer;
  transition: all 0.3s ease;
}

.zero:hover {
  color: #f59e0b;
  transform: scale(1.1);
}

.zero.spin {
  animation: spin 1s ease-in-out;
}

/* Text Styling */
.error-title {
  font-size: 2.5rem;
  color: #374151;
  margin: 1rem 0;
  font-weight: 600;
}

.error-message {
  font-size: 1.2rem;
  color: #6b7280;
  margin-bottom: 3rem;
  line-height: 1.6;
}

/* Button Styling */
.button-group {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 2rem;
}

.btn {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: linear-gradient(45deg, #6366f1, #8b5cf6);
  color: white;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

.btn-secondary {
  background: linear-gradient(45deg, #10b981, #059669);
  color: white;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

.btn-outline {
  background: white;
  color: #6366f1;
  border: 2px solid #6366f1;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-outline:hover {
  background: #6366f1;
  color: white;
  transform: translateY(-2px);
}

.pulse {
  animation: pulse 0.6s ease-in-out infinite alternate;
}

.icon {
  font-size: 1.2rem;
}

/* Fun Facts Section */
.fun-section {
  margin: 2rem 0;
  padding: 1.5rem;
  background: rgba(99, 102, 241, 0.05);
  border-radius: 15px;
  border: 1px solid rgba(99, 102, 241, 0.1);
}

.fun-fact {
  color: #4b5563;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.fun-fact:hover {
  color: #6366f1;
  transform: scale(1.02);
}

.fact-icon {
  font-size: 1.5rem;
}

.click-hint {
  font-size: 0.8rem;
  color: #9ca3af;
  font-style: italic;
}

.toggle-facts {
  background: none;
  border: 1px solid #d1d5db;
  color: #6b7280;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.toggle-facts:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

/* Particles */
.particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.particle {
  position: absolute;
  background: linear-gradient(45deg, #6366f1, #8b5cf6);
  border-radius: 50%;
  opacity: 0.6;
  animation: float linear infinite;
}

/* Animations */
@keyframes shakeAnimation {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px) rotate(-2deg); }
  75% { transform: translateX(5px) rotate(2deg); }
}

@keyframes wiggle {
  0%, 100% { transform: translateX(-50%) rotate(0deg); }
  25% { transform: translateX(-50%) rotate(-10deg); }
  75% { transform: translateX(-50%) rotate(10deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse {
  0% { transform: scale(1); }
  100% { transform: scale(1.05); }
}

@keyframes float {
  0% {
    transform: translateY(100vh) rotate(0deg);
    opacity: 0;
  }
  10%, 90% {
    opacity: 0.6;
  }
  100% {
    transform: translateY(-10px) rotate(360deg);
    opacity: 0;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .error-code {
    font-size: 5rem;
  }
  
  .error-title {
    font-size: 2rem;
  }
  
  .button-group {
    flex-direction: column;
    align-items: center;
  }
  
  .btn {
    width: 100%;
    max-width: 250px;
  }
  
  .fun-fact {
    text-align: center;
    flex-direction: column;
  }
}
</style>