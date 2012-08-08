<?php
/**
 * This component provides a button for opening the add new form provided by {@link GridFieldDetailForm}.
 *
 * @package metadata
 */
class GridFieldAddNewMetadataFieldButton extends GridFieldAddNewButton {

	public function getHTMLFragments($gridField) {
		if(!$this->buttonName) {
			// provide a default button name, can be changed by calling {@link setButtonName()} on this component
			$this->buttonName = _t('GridField.Add', 'Add {name}', array('name' => singleton($gridField->getModelClass())->singular_name()));
		}

		$data = new ArrayData(array(
			'NewLink' => $gridField->Link('add'),
			'ButtonName' => $this->buttonName,
		));

		return array(
			$this->targetFragment => $data->renderWith('GridFieldAddNewbutton'),
		);
	}

}
