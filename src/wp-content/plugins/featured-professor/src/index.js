import './index.scss';

import { useSelect } from '@wordpress/data';
import { useState, useEffect } from 'react';

wp.blocks.registerBlockType('ourplugin/featured-professor', {
    title: 'Professor Callout',
    description:
        'Include a short description and link to a professor of your choice',
    icon: 'welcome-learn-more',
    category: 'common',
    attributes: {
        profId: { type: 'string' },
    },
    edit: EditComponent,
    save: function () {
        return null;
    },
});

function EditComponent({ attributes, setAttributes }) {
    const [profHtml, setProfHtml] = useState('');
    const [loadingProfHtml, setLoadingProfHtml] = useState(true);

    const allProfs = useSelect((select) =>
        select('core').getEntityRecords('postType', 'professor', {
            per_page: -1,
        })
    );

    const updateTheMeta = () => {
        const profsMeta = wp.data
            .select('core/block-editor')
            .getBlocks()
            .filter(
                (block) =>
                    block.name === 'ourplugin/featured-professor' &&
                    block.attributes.profId
            )
            .map((block) => block.attributes.profId);

        wp.data
            .dispatch('core/editor')
            .editPost({ meta: { featuredprofessor: [...new Set(profsMeta)] } });
    };

    useEffect(function () {
        return function () {
            updateTheMeta();
        };
    }, []);

    useEffect(
        function () {
            updateTheMeta();

            const fetchData = async () => {
                setLoadingProfHtml(true);
                try {
                    const response = await fetch(
                        `//localhost:8080/wp-json/university/v1/professors/${attributes['profId']}/as-html`
                    );
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message || 'Oops. Something went wrong.'
                        );
                    }

                    setProfHtml(result.data);
                } catch (error) {
                    console.error(error);
                } finally {
                    setLoadingProfHtml(false);
                }
            };

            attributes['profId'] ? fetchData() : setProfHtml('');
        },
        [attributes['profId']]
    );

    if (!allProfs) return <p>Loading...</p>;

    return (
        <div className="featured-professor-wrapper">
            <div className="professor-select-container">
                <select
                    onChange={(e) => setAttributes({ profId: e.target.value })}
                >
                    <option value="">Select a professor</option>
                    {allProfs.map((prof) => (
                        <option
                            key={prof.id}
                            value={prof.id.toString()}
                            selected={
                                attributes['profId'] === prof.id.toString()
                            }
                        >
                            {prof.title.rendered}
                        </option>
                    ))}
                </select>
            </div>
            <div>
                {loadingProfHtml ? (
                    'loading...'
                ) : (
                    <div dangerouslySetInnerHTML={{ __html: profHtml }} />
                )}
            </div>
        </div>
    );
}
