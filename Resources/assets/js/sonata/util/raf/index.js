
/** @const {time} */
const FRAME_DURATION = 16;

const getRaf = () => {
    const raf = window.requestAnimationFrame || window.webkitRequestAnimationFrame;
    if (raf) {
        return raf.bind(window);
    }

    let lastTime = 0;
    return fn => {
        const now = new Date().getTime();
        // By default we take 16ms between frames,
        // but if the last frame was say 10ms ago, we only want to wait 6ms.
        const timeToCall = Math.max(0, FRAME_DURATION - (now - lastTime));
        lastTime = now + timeToCall;
        window.setTimeout(fn, timeToCall);
    };
};

export default getRaf();
