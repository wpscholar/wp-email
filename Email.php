<?php

namespace wpscholar\WordPress;

/**
 * Class Email
 *
 * @package wpscholar\WordPress
 *
 * @property string $attachments
 * @property string $charset
 * @property string $contentType
 * @property string $from
 * @property string $fromName
 * @property string $header
 * @property string $message
 * @property string $subject
 * @property string $to
 */
class Email {

	/**
	 * @var string
	 */
	protected $from = '';

	/**
	 * @var string
	 */
	protected $fromName = '';

	/**
	 * @var array
	 */
	protected $to = [];

	/**
	 * @var string
	 */
	protected $subject = '';

	/**
	 * @var string
	 */
	protected $message = '';

	/**
	 * @var array
	 */
	protected $headers = [];

	/**
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * @var string
	 */
	protected $charset;

	/**
	 * Instantiates a new email object
	 *
	 * @param string|object|array|null $data
	 */
	public function __construct( $data = null ) {
		if ( ! is_null( $data ) ) {
			$this->setup( $data );
		}
	}

	/**
	 * Sets up the email
	 *
	 * @param string|object|array $data
	 */
	public function setup( $data ) {

		// If data is a string, assume it is JSON
		if ( is_string( $data ) ) {
			$data = json_decode( $data );
		}

		// If data is an object, convert to an array
		if ( is_object( $data ) ) {
			$data = (array) $data;
		}

		// Set the headers
		if ( isset( $data['headers'] ) ) {
			$this->headers = (array) $data['headers'];
		}

		// Set the subject
		if ( isset( $data['subject'] ) ) {
			$this->_set_subject( $data['subject'] );
		}

		// Set the message
		if ( isset( $data['message'] ) ) {
			$this->_set_message( $data['message'] );
		}

		// Set the sender name and/or email
		if ( isset( $data['from'] ) ) {
			$this->_set_from( $data['from'] );
		}

		// Set the recipient(s) name and/or email
		if ( isset( $data['to'] ) ) {
			$to = $data['to'];
			if ( ! is_array( $to ) || isset( $to['email'] ) ) {
				$to = [ $data['to'] ];
			}
			foreach ( $to as $recipient ) {
				$this->addRecipient( $recipient );
			}
		}

		// Set the CC recipient(s) name and/or email
		if ( isset( $data['cc'] ) ) {
			$cc = $data['cc'];
			if ( ! is_array( $cc ) || isset( $cc['email'] ) ) {
				$cc = [ $data['cc'] ];
			}
			foreach ( $cc as $recipient ) {
				$this->addCcRecipient( $recipient );
			}
		}

		// Set the BCC recipient(s) name and/or email
		if ( isset( $data['bcc'] ) ) {
			$bcc = $data['bcc'];
			if ( ! is_array( $bcc ) || isset( $bcc['email'] ) ) {
				$bcc = [ $data['bcc'] ];
			}
			foreach ( $bcc as $recipient ) {
				$this->addBccRecipient( $recipient );
			}
		}

		// Add any attachments
		if ( isset( $data['attachments'] ) ) {
			$this->attachments = (array) $data['attachments'];
		}

		// Set the content type
		if ( isset( $data['content_type'] ) ) {
			$this->contentType = $data['content_type'];
		}

		// Set the character set
		if ( isset( $data['charset'] ) ) {
			$this->charset = $data['charset'];
		}
	}

	/**
	 * Add a header
	 */
	public function addHeader( $header ) {
		$this->headers[] = $header;
	}

	/**
	 * Add a recipient to the email
	 *
	 * @param string|object|array $recipient
	 */
	public function addRecipient( $recipient ) {
		$to = new EmailRecipient( $recipient );
		if ( $to->validate() ) {
			$this->to[] = "{$to}";
		}
	}

	/**
	 * Add a CC recipient to the email
	 *
	 * @param string|object|array $recipient
	 */
	public function addCcRecipient( $recipient ) {
		$cc = new EmailRecipient( $recipient );
		if ( $cc->validate() ) {
			$this->addHeader( "Cc: {$cc}" );
		}
	}

	/**
	 * Add a BCC recipient to the email
	 *
	 * @param string|object|array $recipient
	 */
	public function addBccRecipient( $recipient ) {
		$bcc = new EmailRecipient( $recipient );
		if ( $bcc->validate() ) {
			$this->addHeader( "Bcc: {$bcc}" );
		}
	}

	/**
	 * Add an attachment to the email
	 *
	 * @param string $filePath
	 */
	public function addAttachment( $filePath ) {
		if ( file_exists( $filePath ) ) {
			$this->attachments[] = $filePath;
		}
	}

	/**
	 * Set the email character set
	 *
	 * @param string $charset
	 */
	protected function _set_charset( $charset ) {
		$this->charset = $charset;
	}

	/**
	 * Set the email content type
	 *
	 * @param string $contentType
	 */
	protected function _set_contentType( $contentType ) {
		$this->contentType = $contentType;
	}

	/**
	 * Set the from contact on the email
	 *
	 * @param string|object|array $recipient
	 */
	protected function _set_from( $recipient ) {
		$from = new EmailRecipient( $recipient );
		if ( $from->validate() ) {
			$this->from = $from->email;
		}
		if ( ! empty( $from->name ) ) {
			$this->fromName = $from->name;
		}
	}

	/**
	 * Set the email copy
	 *
	 * @param string $message
	 */
	protected function _set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Set the email subject
	 *
	 * @param string $subject
	 */
	protected function _set_subject( $subject ) {
		$this->subject = html_entity_decode( sanitize_text_field( $subject ) );
	}

	/**
	 * Add filters before email is sent
	 */
	protected function _pre_send() {
		// Add filters
		add_filter( 'wp_mail_from', array( $this, '_wp_mail_from' ) );
		add_filter( 'wp_mail_fromName', array( $this, '_wp_mail_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, '_wp_mail_content_type' ) );
		add_filter( 'wp_mail_charset', array( $this, '_wp_mail_charset' ) );
	}

	/**
	 * Sends the email
	 *
	 * @return bool
	 */
	public function send() {

		$this->_pre_send();

		// Send email
		$sent = wp_mail( $this->to, $this->subject, $this->message, $this->headers, $this->attachments );

		$this->_post_send();

		return $sent;
	}

	/**
	 * Remove filters after email is sent
	 */
	protected function _post_send() {
		// Remove filters
		remove_filter( 'wp_mail_from', array( $this, '_wp_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, '_wp_mail_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, '_wp_mail_content_type' ) );
		remove_filter( 'wp_mail_charset', array( $this, '_wp_mail_charset' ) );
	}

	/**
	 * WordPress callback for setting the from email
	 *
	 * @param string $email
	 *
	 * @return string
	 */
	public function _wp_mail_from( $email ) {
		if ( ! empty( $this->from ) && is_email( $this->from ) ) {
			$email = $this->from;
		}

		return $email;
	}

	/**
	 * WordPress callback for setting the from name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function _wp_mail_from_name( $name ) {
		if ( ! empty( $this->fromName ) ) {
			$name = html_entity_decode( sanitize_text_field( $this->fromName ) );
		}

		return $name;
	}

	/**
	 * WordPress callback for setting the content type
	 *
	 * @param string $contentType
	 *
	 * @return string
	 */
	public function _wp_mail_content_type( $contentType ) {
		if ( ! empty( $this->contentType ) ) {
			$contentType = $this->contentType;
		}

		return $contentType;
	}

	/**
	 * WordPress callback for setting the charset
	 *
	 * @param string $charset
	 *
	 * @return string
	 */
	public function _wp_mail_charset( $charset ) {
		if ( ! empty( $this->charset ) ) {
			$charset = $this->charset;
		}

		return $charset;
	}

	/**
	 * Getter function.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		$value = null;
		$method = "_get_{$property}";
		if ( method_exists( $this, $method ) && is_callable( [ $this, $method ] ) ) {
			$value = $this->$method();
		} else if ( property_exists( $this, $property ) ) {
			$value = $this->{$property};
		}

		return $value;
	}

	/**
	 * Setter function.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set( $property, $value ) {
		$method = "_set_{$property}";
		if ( method_exists( $this, $method ) && is_callable( [ $this, $method ] ) ) {
			$this->$method( $value );
		}
	}

}
