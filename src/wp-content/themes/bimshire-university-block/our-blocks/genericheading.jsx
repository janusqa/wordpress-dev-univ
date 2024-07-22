// NOTE JSX is used here
import { registerBlockType } from "@wordpress/blocks";

//register block "{namespace}/{blockname}"
registerBlockType('ourblocktheme/genericheading', {
    title: 'GenericHeading',
    edit: EditComponent,
    save: SaveComponent,
});

function EditComponent() {
    return (
        <h1 className="headline headline--large">Welcome!</h1>
    );
}

function SaveComponent() {
    return (
        <h1 className="headline headline--large">Welcome!</h1>
    );
}
