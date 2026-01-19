import { ref } from 'vue';

// Shared reactive state
const showSubscribePopup = ref(false);
const subscribeEmail = ref('');
const isSubscribing = ref(false);
const subscribeSuccess = ref('');
const subscribeError = ref('');
const showEmailField = ref(false);

export function useSubscribePopup() {
    const openSubscribePopup = () => {
        showSubscribePopup.value = true;
        subscribeEmail.value = '';
        subscribeSuccess.value = '';
        subscribeError.value = '';
        showEmailField.value = false; // Reset email field visibility when popup opens
    };

    const closeSubscribePopup = () => {
        showSubscribePopup.value = false;
        showEmailField.value = false; // Reset when closing
    };

    const revealEmailField = () => {
        showEmailField.value = true;
    };

    return {
        showSubscribePopup,
        subscribeEmail,
        isSubscribing,
        subscribeSuccess,
        subscribeError,
        showEmailField,
        openSubscribePopup,
        closeSubscribePopup,
        revealEmailField
    };
}
