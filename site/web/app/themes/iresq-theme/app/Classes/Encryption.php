<?php

class Encryption {
  
  private $key;
  private $salt;

  public function __construct() {
    $this->key = $this->get_default_key();
    $this->salt = $this->get_default_salt();
  }

  /**
   * Returns the encrypted version of the given $value,
   * or false if encryption failed.
   *
   * @param [string] $value
   * @return string|bool
   */
  public function encrypt( $value ) {
  
    /**
     * We will be encrypting using the OpenSLL cryptography library so 
     * we need to check if it's loaded.
     */
    if( !extension_loaded( 'openssl' ) ) {
      return $value;
    }

    // The cipher method to use for encryption
    $method = 'aes-256-ctr';

    // The cipher length. Returns false on failure.
    $ivlen = openssl_cipher_iv_length( $method );

    /**
     * The initialization vector, which is a random string with a different 
     * length based on the cipher method
     */
    $iv = openssl_random_pseudo_bytes( $ivlen );

    /*
     * 1. the value to encrypt with the salt appended to it
     * 2. the cipher method for encryption (algorithm)
     * 3. the key to use for encryption
     * 4. option flags. we have none so pass it 0 so we can add a 5th parameter
     * 5. the initialization vector (random string with a different length based on 
     *    the cipher methods)
     */
    $raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
    if( ! $raw_value ) {
      return false;
    }

    /**
     * Prepend the initialization vector to the raw_value so we can determine it again for decryption.
     * 
     * We use base64_encode because the encryption result is a binary string that likely 
     * includes characters that are human-unreadable and may be problematic for being stored in a
     * database.
     */
    return base64_encode( $iv . $raw_value );
  }

  /**
   * Returns the decrypted version of the given $raw_value,
   * or false if decryption failed
   *
   * @param [string] $raw_value
   * @return string|bool
   */
  public function decrypt( $raw_value ) {
    /**
     * We will be encrypting using the OpenSLL cryptography library so 
     * we need to check if it's loaded.
     */
    if( ! extension_loaded( 'openssl' ) ) {
      return $raw_value;
    }

    // Get the binary version again
    $raw_value = base64_decode( $raw_value, true );

    // Get the cipher method for decryption
    $method = 'aes-256-ctr';

    // The cipher length. Returns false if failed.
    $ivlen = openssl_cipher_iv_length( $method );

    /**
     * The initialization vector, which is a random string with a different 
     * length based on the cipher method.
     * 
     * Instead of calling openssl_random_pseudo_bytes here, remove it from
     * the binary version (remember, we prepended it to the encryption result before).
     * 
     * This value will also be passed as the 5th parameter in the openssl_decrypt call
     * so that it matches what was passed when encrypting.
     */
    $iv = substr( $raw_value, 0, $ivlen );

    $raw_value = substr( $raw_value, $ivlen );

    /*
     * 1. the value to encrypt with the salt appended to it
     * 2. the cipher method for encryption (algorithm)
     * 3. the key to use for encryption
     * 4. option flags. we have none so pass it 0 so we can add a 5th parameter
     * 5. the initialization vector (random string with a different length based on 
     *    the cipher methods)
     */
    $value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );

    if (! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
      return false;
    }

    return substr( $value, 0, - strlen( $this->salt ) );
  }

  /**
   * Returns the master key for encyption. This should be set in the 
   * environments config. If it's not found, we will use the WordPress
   * default LOGGED_IN_KEY.
   * 
   * Once these values are set, they should never be edited.
   * 
   * If the FILEMAKER_ENCRYPTION_KEY or LOGGED_IN_KEY cannot be found,
   * there is a serious security issue going on.
   *
   * @return string
   */
  private function get_default_key() {

    /**
     * This value is set in the environment variables and should never be edited
     */
    if ( defined( $_ENV['FILEMAKER_ENCRYPTION_KEY'] ) && '' !== $_ENV['FILEMAKER_ENCRYPTION_KEY'] ) {
      return $_ENV['FILEMAKER_ENCRYPTION_KEY'];
    }

    /**
     * This is a fallback in case FILEMAKER_ENCRYPTION_KEY is ever removed.
     * 
     * This values come from our wordpress core installation.
     */
    if( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
      return LOGGED_IN_KEY;
    }

    // if this is reached, you're either not on a live site or have a serious security issue
    return 'this-is-not-a-secret-key';
  }

  /**
   * Returns the master salt key for encyption. This should be set in the 
   * environments config. If it's not found, we will use the WordPress
   * default LOGGED_IN_SALT.
   * 
   * This SALT key will be appended to the encrypted value for extra
   * security.
   * 
   * If the FILEMAKER_ENCRYPTION_SALT or LOGGED_IN_SALT cannot be found,
   * there is a serious security issue going on.
   *
   * @return string
   */
  private function get_default_salt() {
    
    /**
     * This value is set in the environment variables and should never be edited
     */
    if ( defined( $_ENV['FILEMAKER_ENCRYPTION_SALT'] ) && '' !== $_ENV['FILEMAKER_ENCRYPTION_SALT'] ) {
      return $_ENV['FILEMAKER_ENCRYPTION_SALT'];
    }

    /**
     * This is a fallback in case FILEMAKER_ENCRYPTION_SALT is ever removed.
     * 
     * This values come from our wordpress core installation.
     */
    if( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
      return LOGGED_IN_SALT;
    }

    // if this is reached, you're either not on a live site or have a serious security issue
    return 'this-is-not-a-secret-salt';
  }
}