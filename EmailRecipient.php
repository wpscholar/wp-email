<?php

namespace wpscholar\WordPress;

/**
 * Class EmailRecipient
 *
 * @package wpscholar\WordPress
 */
class EmailRecipient {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * Instantiates a new email recipient object
	 *
	 * @param string|object|array|null $recipient
	 */
	public function __construct( $recipient = null ) {
		if ( ! is_null( $recipient ) ) {
			$this->set( $recipient );
		}
	}

	/**
	 * Setup the email recipient properties
	 *
	 * @param string|object|array $recipient
	 */
	public function set( $recipient ) {
		switch ( gettype( $recipient ) ) {
			case 'string':
				$this->parse( $recipient );
				break;
			case 'object':
				$recipient = (array) $recipient; // Convert and continue to 'array' case
			case 'array':
				if ( isset( $recipient['name'] ) ) {
					$this->name = $recipient['name'];
				}
				if ( isset( $recipient['email'] ) ) {
					$this->email = $recipient['email'];
				}
				break;
		}
	}

	/**
	 * Parse a text recipient
	 *
	 * @param string $text
	 */
	public function parse( $text ) {
		$parts = explode( ' ', $text );
		$this->email = trim( array_pop( $parts ), '<>' );
		$this->name = join( ' ', $parts );
	}

	/**
	 * Validate the recipient email
	 *
	 * @return bool|string
	 */
	public function validate() {
		return is_email( $this->email );
	}

	/**
	 * Convert the object into a string
	 *
	 * @return string
	 */
	public function __toString() {
		return empty( $this->name ) ? $this->email : "{$this->name} <{$this->email}>";
	}

}
