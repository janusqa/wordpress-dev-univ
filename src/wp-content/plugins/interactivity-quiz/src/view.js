/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

const { state } = store('create-block', {
    actions: {
        // buttonHandler: () => {
        //     const context = getContext();
        //     context.clickCount++;
        // },
        // toggle: () => {
        //     const context = getContext();
        //     context.isOpen = !context.isOpen;
        // },
        onSelectAnswer: () => {
            const context = getContext();

            const resetIsIncorrect = () => {
                const incorrectMessageElement = document
                    .getElementById(context.blockId)
                    .querySelector('.incorrect-message');
                if (incorrectMessageElement) {
                    const handleAnimationEnd = () => {
                        context.isIncorrect = false;

                        // Remove the event listener after it has been executed
                        incorrectMessageElement.removeEventListener(
                            'animationend',
                            handleAnimationEnd
                        );
                    };

                    incorrectMessageElement.addEventListener(
                        'animationend',
                        handleAnimationEnd
                    );
                }
            };

            if (!context.isAnswered) {
                context.isCorrect =
                    context.correctAnswer === context.answerIndex;
                context.isIncorrect = !context.isCorrect;
                if (context.isCorrect) {
                    state.solvedCount++;
                    setTimeout(
                        () => (context.isAnswered = context.isCorrect),
                        750
                    );
                }
                if (context.isIncorrect) resetIsIncorrect();
            }
        },
    },
    callbacks: {
        fadedClass: () => {
            const context = getContext();
            return (
                context.isAnswered &&
                context.answerIndex !== context.correctAnswer
            );
        },
        noClickClass: () => {
            const context = getContext();
            return (
                context.isAnswered &&
                context.answerIndex === context.correctAnswer
            );
        },
        isAnswer: () => {
            const context = getContext();
            return context.answerIndex === context.correctAnswer;
        },
        // logIsOpen: () => {
        //     const { isOpen } = getContext();
        //     // Log the value of `isOpen` each time it changes.
        //     console.log(`Is open: ${isOpen}`);
        // },
    },
});
