<?php
class UriMigration extends Migration {

	function describe() {
		$fields = "path=\ntype=string,length=64\t,";
		$fields .= "template=\ntype=string,length=32\t,";
		$fields .= "visible=\ntype=int,length=2,default=1,input_type=select,range=0:1\t,";
		$fields .= "importance=\ntype=int,length=2,default=0,input_type=select,range=0:10\t";
		return $fields;
	}

	function up() {
		$elementTable =  rA($this->describe());
		$this->create_table("uris", $elementTable);
		$this->table_insert("uris", "path, template, security", "'elements', 'Starbug', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'uris/new', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'uris/get', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'uris/edit', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'uris/add', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, security", "'users', 'Starbug', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'users/new', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'users/get', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'users/edit', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'users/add', 'Ajax', '0', '4'");
		$this->table_insert("uris", "path, template, visible, security", "'login', 'Starbug', '0', '0'");
	}

	function down() {
		$this->drop_table("uris");
	}

}
?>