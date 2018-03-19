/**
 * LifterLMS JS Builder App Bootstrap
 * @since    3.16.0
 * @version  [version]
 */
require( [
	'../vendor/backbone.collectionView',
	'../vendor/backbone-forms',
	'../vendor/backbone.trackit',
	'Controllers/Construct',
	'Controllers/Debug',
	'Controllers/Sync',
	'Models/loader',
	'Views/Editors/wysiwyg',
	'Views/Course',
	'Views/Sidebar'
], function(
	Forms,
	CV,
	TrackIt,
	Construct,
	Debug,
	Sync,
	Models,
	WysiwygEditor,
	CourseView,
	SidebarView
) {

	window.llms_builder.debug = new Debug( window.llms_builder.debug );
	window.llms_builder.construct = new Construct();

	/**
	 * Underscores templating utilities
	 * @since    [version]
	 * @version  [version]
	 */
	_.mixin( {

		/**
		 * Determine if two values are equal and output checked attribute if they are
		 * Useful for templating checkboxes & radio elements
		 * Like WP Core PHP checked() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		checked: function( expected, actual ) {
			if ( expected == actual ) {
				return ' checked="checked"';
			}
			return '';
		},

		/**
		 * Determine if two values are equal and output seleted attribute if they are
		 * Useful for templating select elements
		 * Like WP Core PHP selected() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    [version]
		 * @version  [version]
		 */
		selected: function( expected, actual ) {
			if ( expected == actual ) {
				return ' selected="selected"';
			}
			return '';
		},
	} );

	// register custom backbone forms editor
	Backbone.Form.editors.Wysiwyg = WysiwygEditor;

	Backbone.pubSub = _.extend( {}, Backbone.Events );

	$( document ).trigger( 'llms-builder-pre-init' );

	window.llms_builder.questions = window.llms_builder.construct.get_collection( 'QuestionTypes', window.llms_builder.questions );

	var CourseModel = window.llms_builder.construct.get_model( 'Course', window.llms_builder.course );
	window.llms_builder.CourseModel = CourseModel;

	window.llms_builder.sync = new Sync( CourseModel, window.llms_builder.sync );

	var Course = new CourseView( {
		model: CourseModel,
	} );

	var Sidebar = new SidebarView( {
		CourseView: Course
	} );

	$( document ).trigger( 'llms-builder-init', {
		course: Course,
		sidebar: Sidebar,
	} );

} );
