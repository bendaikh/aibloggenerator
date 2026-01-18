import { ref } from 'vue';

// Shared reactive state
const showSubscribePopup = ref(false);
const subscribeEmail = ref('');
const isSubscribing = ref(false);
const subscribeSuccess = ref('');
const subscribeError = ref('');

export function useSubscribePopup() {
    const openSubscribePopup = () => {
        showSubscribePopup.value = true;
        subscribeEmail.value = '';
        subscribeSuccess.value = '';
        subscribeError.value = '';
    };

    const closeSubscribePopup = () => {
        showSubscribePopup.value = false;
    };

    return {
        showSubscribePopup,
        subscribeEmail,
        isSubscribing,
        subscribeSuccess,
        subscribeError,
        openSubscribePopup,
        closeSubscribePopup
    };
}
