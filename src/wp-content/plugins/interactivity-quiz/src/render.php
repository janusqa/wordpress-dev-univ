<?php

/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
$block_answers = array();
foreach ($attributes['answers'] as $key => $answer) {
	$block_answers[] = array('answerIndex' => $key, 'answerText' => $answer);
}

$block_context = array(
	'answers' => $block_answers,
	'correctAnswer' => $attributes['answer'],
	'isCorrect' => false,
	'isIncorrect' => false,
	'isAnswered' => false
);
?>

<!-- 
	"ddata-wp-interactive" is the attribute that wires up the renderer here to the frontend view.js..
	view.js defines a react context (state) and we give the "ddata-wp-interactive" a value of the name
	of this state as seen in view.js. That enables the connectivity between this renderer and view.js.
	Note "create-block" is not a special name, It's just the name given to the context by default when
	usint "create-block" script to scaffold a plugin.

	You can give a wp block pieces of context like data-wp-context='{"clickCount": 20}'. NOTE PAY ATTENTION
	to how it is formatted.  Then any place within that wp block you can access the context.
	For example to acces the context as a text value use attribute data-wp-text="context.clickCount"
	Now from javascript in view.js you can manipulate that context

	"data-wp-on" attribute is a wordpress attribute via which you can set up event handlers.
	You can have "data-wp-on--click", "data-wp-on--keyup", etc. You get the picture.
-->
<!--
<div data-wp-interactive="create-block" data-wp-context='{"clickCount": 20}'>
	<p>The button below has been clicked <span data-wp-text="context.clickCount"></span> times</p>
	<button data-wp-on--click="actions.buttonHandler">Click me</button>
</div>
-->

<div style="background-color: <?php echo $attributes['bgColor'] ?>; text-align: <?php echo $attributes['titleAlignment'] ?>;" class="paying-attention-view" data-wp-interactive="create-block" <?php echo wp_interactivity_data_wp_context($block_context) ?>>
	<p><?php echo $attributes['question'] ?></p>
	<ul>
		<!--
		1. This is one way of handling interactivity using interactivit API, i.e. clicking ect.
		Use this method if manipulating the dom
		<template data-wp-each="context.answers">
			<li data-wp-on--click="actions.onSelectAnswer" data-wp-text="context.item"></li>
		</template>
		-->

		<!-- 
		2. This second approach blends traditional php with interactivity API
		-->
		<?php foreach ($block_answers as $answer) { ?>
			<li data-wp-class--no-click="callbacks.noClickClass" data-wp-class--fade-incorrect="callbacks.fadedClass" <?php echo wp_interactivity_data_wp_context($answer) ?> data-wp-on--click="actions.onSelectAnswer">
				<span data-wp-bind--hidden="!context.isAnswered">
					<span data-wp-bind--hidden="!callbacks.isAnswer">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-check" viewBox="0 0 16 16">
							<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z" />
						</svg>
					</span>
					<span data-wp-bind--hidden="callbacks.isAnswer">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-x" viewBox="0 0 16 16">
							<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
						</svg>
					</span>
				</span>
				&nbsp;<?php echo $answer['answerText'] ?>
			</li>
		<?php } ?>
	</ul>
	<div class="correct-message" data-wp-class--correct-message--visible="context.isCorrect">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="bi bi-emoji-smile" viewBox="0 0 16 16">
			<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
			<path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.5 3.5 0 0 0 8 11.5a3.5 3.5 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5" />
		</svg>
		<p>That is correct!</p>
	</div>
	<div class="incorrect-message" data-wp-class--incorrect-message--visible="context.isIncorrect">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="bi bi-emoji-frown" viewBox="0 0 16 16">
			<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
			<path d="M4.285 12.433a.5.5 0 0 0 .683-.183A3.5 3.5 0 0 1 8 10.5c1.295 0 2.426.703 3.032 1.75a.5.5 0 0 0 .866-.5A4.5 4.5 0 0 0 8 9.5a4.5 4.5 0 0 0-3.898 2.25.5.5 0 0 0 .183.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5" />
		</svg>
		<p>Sorry, try again!</p>
	</div>
</div>