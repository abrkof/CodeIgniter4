<?php namespace CodeIgniter\Mail;

abstract class BaseHandler implements MailHandlerInterface
{
    /**
     * Used as the User-Agent and X-Mailer headers' value.
     *
     * @var    string
     */
    public $useragent = 'CodeIgniter';

    /**
     * Message format.
     *
     * @var    string    'text' or 'html'
     */
    public $mailtype = 'text';

    /**
     * Character set (default: utf-8)
     *
     * @var    string
     */
    public $charset = 'utf-8';

    /**
     * Whether to validate e-mail addresses.
     *
     * @var    bool
     */
    public $validate = true;

    /**
     * X-Priority header value.
     *
     * @var    int    1-5
     */
    public $priority = 3;            // Default priority (1 - 5)

    /**
     * Newline character sequence.
     * Use "\r\n" to comply with RFC 822.
     *
     * @link    http://www.ietf.org/rfc/rfc822.txt
     * @var    string    "\r\n" or "\n"
     */
    public $newline = "\n";            // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)

    /**
     * CRLF character sequence
     *
     * RFC 2045 specifies that for 'quoted-printable' encoding,
     * "\r\n" must be used. However, it appears that some servers
     * (even on the receiving end) don't handle it properly and
     * switching to "\n", while improper, is the only solution
     * that seems to work for all environments.
     *
     * @link    http://www.ietf.org/rfc/rfc822.txt
     * @var    string
     */
    public $crlf = "\n";

    /**
     * Whether to use Delivery Status Notification.
     *
     * @var    bool
     */
    public $DSN = false;

    /**
     * Whether to send multipart alternatives.
     * Yahoo! doesn't seem to like these.
     *
     * @var    bool
     */
    public $sendMultipart = true;

    /**
     * Whether to send messages to BCC recipients in batches.
     *
     * @var    bool
     */
    public $BCCBatchMode = false;

    /**
     * BCC Batch max number size.
     *
     * @see    CI_Email::$bcc_batch_mode
     * @var    int
     */
    public $BCCBatchSize = 200;

    //--------------------------------------------------------------------

    /**
     * Whether to perform SMTP authentication
     *
     * @var    bool
     */
    protected $SMTPAuth = false;

    /**
     * Whether to send a Reply-To header
     *
     * @var    bool
     */
    protected $ReplyToFlag = false;

    /**
     * Debug messages
     *
     * @see    CI_Email::print_debugger()
     * @var    string
     */
    protected $debugMsg = [];

    /**
     * Recipients
     *
     * @var    string[]
     */
    protected $recipients = [];

    /**
     * CC Recipients
     *
     * @var    string[]
     */
    protected $CC = [];

    /**
     * BCC Recipients
     *
     * @var    string[]
     */
    protected $BCC = [];

    /**
     * Message headers
     *
     * @var    string[]
     */
    protected $headers = [];

    /**
     * Attachment data
     *
     * @var    array
     */
    protected $attachments = [];

    /**
     * mbstring.func_override flag
     *
     * @var bool
     */
    protected static $funcOverride;

    //--------------------------------------------------------------------

    public function __construct(array $config=[])
    {
        $this->reset();

        foreach ($config as $key => $value)
        {
            if (isset($this->$key))
            {
                $this->$key = $value;
            }
        }

        if (! isset(self::$funcOverride))
        {
            self::$funcOverride = (extension_loaded('mbstring') && ini_get('mbstring.func_override'));
        }

        $this->charset = strtoupper($this->charset);
    }

    /**
     * Sets the Mail Message class that represents the message details.
     *
     * @param \CodeIgniter\Mail\BaseMessage $message
     *
     * @return mixed
     */
    public function setMessage(BaseMessage $message)
    {

    }

    //--------------------------------------------------------------------

    /**
     * Does the actual delivery of a message.
     *
     * @param bool $clear_after If TRUE, will reset the class after sending.
     *
     * @return mixed
     */
    public abstract function send(bool $clear_after = true);

    //--------------------------------------------------------------------

    /**
     * Adds an attachment to the current email that is being built.
     *
     * @param string $filename
     * @param string $disposition like 'inline'. Default is 'attachment'
     * @param string $newname     If you'd like to rename the file for delivery
     * @param string $mime        Custom defined mime type.
     */
    public function attach(string $filename, string $disposition = null, string $newname = null, string $mime = null)
    {
        return;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a header value for the email. Not every service will provide this.
     *
     * @param $field
     * @param $value
     *
     * @return mixed
     */
    public function setHeader(string $field, $value)
    {
        $this->headers[$field] = $value;

        return $this;
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Options
    //--------------------------------------------------------------------

    /**
     * Sets the email address to send the email to.
     *
     * @param $email
     *
     * @return mixed
     */
    public function to(string $email)
    {
        $this->to = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets who the email is coming from.
     *
     * @param      $email
     * @param null $name
     *
     * @return mixed
     */
    public function from(string $email, string $name = null)
    {
        if (! empty($name))
        {
            $this->from = [$email, $name];
        } else
        {
            $this->from = $email;
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a single additional email address to 'cc'.
     *
     * @param $email
     *
     * @return mixed
     */
    public function CC(string $email)
    {
        $this->cc = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets a single email address to 'bcc' to.
     *
     * @param $email
     *
     * @return mixed
     */
    public function BCC(string $email)
    {
        $this->bcc = $email;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the reply to address.
     *
     * @param $email
     *
     * @return mixed
     */
    public function replyTo(string $email, string $name = null)
    {
        if (! empty($name))
        {
            $this->reply_to = [$email, $name];
        } else
        {
            $this->reply_to = $email;
        }

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the subject line of the email.
     *
     * @param $subject
     *
     * @return mixed
     */
    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the HTML portion of the email address. Optional.
     *
     * @param $message
     *
     * @return mixed
     */
    public function messageHTML(string $message)
    {
        $this->html_message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the text portion of the email address. Optional.
     *
     * @param $message
     *
     * @return mixed
     */
    public function messageText(string $message)
    {
        $this->text_message = $message;

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Sets the format to send the email in. Either 'html' or 'text'.
     *
     * @param $format
     *
     * @return mixed
     */
    public function format(string $format)
    {
        $this->format = $format;

        return $this;
    }

    //--------------------------------------------------------------------
    /**
     * Resets the state to blank, ready for a new email. Useful when
     * sending emails in a loop and you need to make sure that the
     * email is reset.
     *
     * @param bool $clear_attachments
     *
     * @return mixed
     */
    public function reset(bool $clear_attachments = true)
    {
        $this->to           = null;
        $this->from         = null;
        $this->reply_to     = null;
        $this->cc           = null;
        $this->bcc          = null;
        $this->subject      = null;
        $this->html_message = null;
        $this->text_message = null;
        $this->headers      = [];

        return $this;
    }

    //--------------------------------------------------------------------
}
