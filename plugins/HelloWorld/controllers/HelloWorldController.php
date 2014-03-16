<?php

class HelloWorldController extends BaseController {

	public function getIndex()
	{
		return $this->theme->scope('home.index')->render();
	}

}