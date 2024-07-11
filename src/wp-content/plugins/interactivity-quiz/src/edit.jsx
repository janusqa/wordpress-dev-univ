import {
    TextControl,
    Flex,
    FlexBlock,
    FlexItem,
    Button,
    Icon,
    PanelBody,
    PanelRow,
} from '@wordpress/components';
import {
    InspectorControls,
    BlockControls,
    AlignmentToolbar,
} from '@wordpress/block-editor';
import { ChromePicker } from 'react-color';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();

    return (
        <div {...blockProps}>
            <div
                className="paying-attention-edit-block"
                style={{ backgroundColor: attributes.bgColor }}
            >
                <BlockControls>
                    <AlignmentToolbar
                        value={attributes.titleAlignment}
                        onChange={(align) =>
                            setAttributes({ titleAlignment: align })
                        }
                    />
                </BlockControls>
                <InspectorControls>
                    <PanelBody title="Background Color" InitialOpen={true}>
                        <PanelRow>
                            <ChromePicker
                                color={attributes.bgColor}
                                onChangeComplete={(newColor) =>
                                    setAttributes({ bgColor: newColor.hex })
                                }
                                disableAlpha={true}
                            />
                        </PanelRow>
                    </PanelBody>
                </InspectorControls>
                <TextControl
                    label="Question:"
                    value={attributes.question}
                    onChange={(value) => setAttributes({ question: value })}
                    style={{ fontSize: '20px' }}
                />
                <p style={{ fontSize: '13px', margin: '20px 0 8px 0' }}>Answers:</p>
                {attributes.answers.map((answer, idx) => (
                    <Flex>
                        <FlexBlock>
                            <TextControl
                                value={answer}
                                onChange={(newValue) => {
                                    const newAnswers = [...attributes.answers];
                                    newAnswers[idx] = newValue;
                                    setAttributes({ answers: newAnswers });
                                }}
                            />
                        </FlexBlock>
                        <FlexItem>
                            <Button onClick={() => setAttributes({ answer: idx })}>
                                <Icon
                                    className="mark-as-correct"
                                    icon={
                                        attributes.answer === idx
                                            ? 'star-filled'
                                            : 'star-empty'
                                    }
                                />
                            </Button>
                        </FlexItem>
                        <FlexItem>
                            <Button
                                variant="link"
                                className="attention-delete"
                                onClick={() => {
                                    setAttributes({
                                        answers: attributes.answers.filter(
                                            (_, fidx) => fidx !== idx
                                        ),
                                    });
                                    if (idx === attributes.answer)
                                        setAttributes({ answer: undefined });
                                }}
                            >
                                Delete
                            </Button>
                        </FlexItem>
                    </Flex>
                ))}
                <Button
                    variant="primary"
                    onClick={() => {
                        setAttributes({ answers: [...attributes.answers, ''] });
                    }}
                >
                    Add another answer
                </Button>
            </div>
        </div>
    );
}
