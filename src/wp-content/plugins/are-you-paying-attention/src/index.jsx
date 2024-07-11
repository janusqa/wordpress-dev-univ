import "./index.scss";

import {
    TextControl,
    Flex,
    FlexBlock,
    FlexItem,
    Button,
    Icon,
    PanelBody,
    PanelRow,
} from "@wordpress/components";
import { InspectorControls, BlockControls, AlignmentToolbar } from "@wordpress/block-editor";
import { ChromePicker } from "react-color";

(() => {
    let locked = false;

    wp.data.subscribe(() => {
        const shouldLock = wp.data.select("core/block-editor")
            .getBlocks()
            .some(block => (block.name === 'janusplugin/are-you-paying-attention' && block.attributes.answer === undefined));

        if (shouldLock && !locked) {
            locked = true;
            wp.data.dispatch('core/editor').lockPostSaving("noanswer");
        } else if (!shouldLock && locked) {
            locked = false;
            wp.data.dispatch('core/editor').unlockPostSaving("noanswer");
        }

    });
})();


const EditComponent = ({ attributes, setAttributes }) => {
    const updateQuestion = (value) => {
        setAttributes({ question: value })
    };

    return (
        <div className="paying-attention-edit-block" style={{ backgroundColor: attributes.bgColor }}>
            <BlockControls>
                <AlignmentToolbar value={attributes.titleAlignment} onChange={(align) => setAttributes({ titleAlignment: align })} />
            </BlockControls>
            <InspectorControls>
                <PanelBody title="Background Color" InitialOpen={true}>
                    <PanelRow>
                        <ChromePicker
                            color={attributes.bgColor}
                            onChangeComplete={(newColor) => setAttributes({ bgColor: newColor.hex })}
                            disableAlpha={true}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <TextControl label="Question:" value={attributes.question} onChange={updateQuestion} style={{ fontSize: "20px" }} />
            <p style={{ fontSize: "13px", margin: "20px 0 8px 0" }}>Answers:</p>
            {attributes.answers.map((answer, idx) => (
                <Flex>
                    <FlexBlock><TextControl value={answer} onChange={newValue => {
                        const newAnswers = [...attributes.answers];
                        newAnswers[idx] = newValue;
                        setAttributes({ answers: newAnswers });
                    }} /></FlexBlock>
                    <FlexItem><Button onClick={() => setAttributes({ answer: idx })}>
                        <Icon className="mark-as-correct" icon={attributes.answer === idx ? "star-filled" : "star-empty"} />
                    </Button></FlexItem>
                    <FlexItem><Button variant="link" className="attention-delete" onClick={() => {
                        setAttributes({ answers: attributes.answers.filter((_, fidx) => fidx !== idx) });
                        if (idx === attributes.answer) setAttributes({ answer: undefined });
                    }}>Delete</Button></FlexItem>
                </Flex>
            ))}
            <Button variant="primary" onClick={() => {
                setAttributes({ answers: [...attributes.answers, ""] })
            }}>Add another answer</Button>
        </div>
    );
}

const SaveComponent = ({ attributes, setAttributes }) => null;

//1st parameter is the shortname/varible name for our blocktype in the form of {:uniquenamespace}/{:shortnameofourblocktype}
//2nd parameter is a configuration object
//!!!NOTE!!! the save function for this block returns null as the rendering of the front end part is done in php to make this
// plugin more dynamic in what it returns
wp.blocks.registerBlockType('janusplugin/are-you-paying-attention', {
    title: 'Are You Paying Attention?',
    icon: 'smiley',
    category: 'common',
    description: "Give your audience a chance to prove their compreshension.",
    example: {
        attributes: {
            question: "What is my name?",
            answer: 3,
            answers: ['Meowsalot', 'BarksAlot', 'Froggerson', 'Swimley'],
            titleAlignment: "center",
            bgColor: "#CFE8F1"
        }
    },
    attributes: {
        question: { type: "string" },
        answers: { type: "array", default: [""] },
        answer: { type: "integer", default: undefined },
        bgColor: { type: "string", default: "#EBEBEB" },
        titleAlignment: { type: "string", default: "left" }
    },
    // "edit": what you will see in the admin post editor screen
    edit: EditComponent,
    // "save": what the public will see in content
    save: SaveComponent,
});
