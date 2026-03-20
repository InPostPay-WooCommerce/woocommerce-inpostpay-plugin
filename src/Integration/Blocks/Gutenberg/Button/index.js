import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType(metadata.name, {
  ...metadata,
  title: __('InPost Pay Button', 'inpost-pay'),
  description: __('Add InPost Pay button to your content', 'inpost-pay'),
  edit: Edit,
  save: Save,
});
