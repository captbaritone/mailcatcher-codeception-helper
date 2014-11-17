<?php

namespace Codeception\Module;


class MailCatcherHelper extends \Codeception\Module
{
  /**
   * @var \GuzzleHttp\Client
   */
  protected $mailcatcher;


  /**
   * @var array
   */
  protected $config = array('url', 'port');

  /**
   * @var array
   */
  protected $requiredFields = array('url', 'port');

  public function _initialize() {
    $url = $this->config['url'].':'.$this->config['port'];
    print $url;
    $client = new \GuzzleHttp\Client(['base_url' => $url]);
    $this->mailcatcher = $client;
  }


  /**
   * Reset emails
   *
   * Clear all emails from mailcatcher. You probably want to do this before
   * you do the thing that will send emails
   *
   * @return void
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  public function resetEmails()
  {
    $this->mailcatcher->delete('/messages');
  }


  /**
   * See In Last Email
   *
   * Look for a string in the most recent email
   *
   * @return void
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  public function seeInLastEmail($expected)
  {
    $email = $this->lastMessage();
    $this->seeInEmail($email, $expected);
  }

  /**
   * See In Last Email To
   *
   * Look for a string in the most recent email sent to $address
   *
   * @return void
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  public function seeInLastEmailTo($address, $expected)
  {
    $email = $this->lastMessageFrom($address);
    $this->seeInEmail($email, $expected);

  }

  // ----------- HELPER METHODS BELOW HERE -----------------------//

  /**
   * Messages
   *
   * Get an array of all the message objects
   *
   * @return array
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  protected function messages()
  {
    $response = $this->mailcatcher->get('/messages');
    $messages = $response->json();
    if (empty($messages)) {
      $this->fail("No messages received");
    }

    return $messages;
  }

  /**
   * Last Message
   *
   * Get the most recent email
   * @return obj
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  protected function lastMessage()
  {
    $messages = $this->messages();

    $last = array_shift($messages);

    return $this->emailFromId($last['id']);
  }

  /**
   * Last Message From
   *
   * Get the most recent email sent to $address
   *
   * @return obj
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  protected function lastMessageFrom($address)
  {
    $messages = $this->messages();
    foreach($messages as $message)
    {
      foreach($message['recipients'] as $recipient)
      {
        if(strpos($recipient, $address) !== false)
        {
          return $this->emailFromId($message['id']);
        }
      }
    }
    $this->fail("No messages sent to {$address}");
  }

  /**
   * Email from ID
   *
   * Given a mailcatcher id, returns the email's object
   *
   * @return obj
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  protected function emailFromId($id)
  {
    $response = $this->mailcatcher->get("/messages/{$id}.json");
    return $response->json();
  }

  /**
   * See In Email
   * Look for a string in an email
   * @return void
   * @author Jordan Eldredge <jordaneldredge@gmail.com>
   **/
  protected function seeInEmail($email, $expected)
  {
    $this->assertContains($expected, $email['source'],  "Email Contains");
  }

}
