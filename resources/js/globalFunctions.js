import { useToast } from 'vue-toastification';
import { h } from 'vue';

const toast = useToast();

const notify = (Message, Type) => {
    toast(h('div', { innerHTML: Message }), {
        type: Type,
        position: "top-right",
        timeout: 2000,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: true,
        closeButton: "button",
        icon: true,
        rtl: false
    });
}

const notify2 = (Message, Type) => {
    toast(h('div', { innerHTML: Message }), {
        type: Type,
        position: "top-right",
        timeout: 4000,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: true,
        closeButton: "button",
        icon: true,
        rtl: false
    });
}

// Function to convert image to base64
const imageToBase64 = (imagePath) => {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous'; // Handle CORS if needed
        
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set canvas dimensions to match image
            canvas.width = img.width;
            canvas.height = img.height;
            
            // Draw image on canvas
            ctx.drawImage(img, 0, 0);
            
            // Convert to base64
            const base64 = canvas.toDataURL('image/png');
            resolve(base64);
        };
        
        img.onerror = (error) => {
            reject(new Error(`Failed to load image: ${error}`));
        };
        
        img.src = imagePath;
    });
};

// Function specifically for your logo
const getLogoBase64 = async () => {
    try {
        const base64Logo = await imageToBase64('/logo1.png');
        return base64Logo;
    } catch (error) {
        console.error('Error converting logo to base64:', error);
        return null;
    }
};

// Cache for storing converted images to avoid repeated conversions
const imageCache = new Map();

// Enhanced function with caching
const getImageBase64 = async (imagePath) => {
    // Check if image is already cached
    if (imageCache.has(imagePath)) {
        return imageCache.get(imagePath);
    }
    
    try {
        const base64Image = await imageToBase64(imagePath);
        // Cache the result
        imageCache.set(imagePath, base64Image);
        return base64Image;
    } catch (error) {
        console.error(`Error converting image ${imagePath} to base64:`, error);
        return null;
    }
};

const globalFunctions = {
    install(app) {
        app.config.globalProperties.notify = notify;
        app.config.globalProperties.notify2 = notify2;
        app.config.globalProperties.imageToBase64 = imageToBase64;
        app.config.globalProperties.getLogoBase64 = getLogoBase64;
        app.config.globalProperties.getImageBase64 = getImageBase64;
    }
};

export { notify, notify2, imageToBase64, getLogoBase64, getImageBase64 };
export default globalFunctions;