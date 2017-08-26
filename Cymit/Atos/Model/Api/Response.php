<?php

namespace Cymit\Atos\Model\Api;
class Response
{


    public function __construct(
    ) {
    }

    public function doResponse($data, $parameters)
    {
        // Récupération de la variable cryptée DATA
        $message = "message=$data";

        // Initialisation du chemin du fichier pathfile
        $pathfile = "pathfile=" . $parameters['pathfile'];

        // Initialisation du chemin de l'executable response
        $binPath = $parameters['bin_response'];

        // Appel du binaire response
        $command = "$binPath $pathfile $message";
        $result = shell_exec($command);

        // On separe les differents champs et on les met dans une variable tableau
        $sips_response = explode('!', $result);

        // Récupération des données de la réponse
        $hash = array();

        list (,
                $hash['code'],
                $hash['error'],
                $hash['merchant_id'],
                $hash['merchant_country'],
                $hash['amount'],
                $hash['transaction_id'],
                $hash['payment_means'],
                $hash['transmission_date'],
                $hash['payment_time'],
                $hash['payment_date'],
                $hash['response_code'],
                $hash['payment_certificate'],
                $hash['authorisation_id'],
                $hash['currency_code'],
                $hash['card_number'],
                $hash['cvv_flag'],
                $hash['cvv_response_code'],
                $hash['bank_response_code'],
                $hash['complementary_code'],
                $hash['complementary_info'],
                $hash['return_context'],
                $hash['caddie'], // unavailable with NO_RESPONSE_PAGE
                $hash['receipt_complement'],
                $hash['merchant_language'], // unavailable with NO_RESPONSE_PAGE
                $hash['language'],
                $hash['customer_id'], // unavailable with NO_RESPONSE_PAGE
                $hash['order_id'],
                $hash['customer_email'], // unavailable with NO_RESPONSE_PAGE
                $hash['customer_ip_address'], // unavailable with NO_RESPONSE_PAGE
                $hash['capture_day'],
                $hash['capture_mode'],
                $hash['data'],
                $hash['order_validity'],
                $hash['transaction_condition'],
                $hash['statement_reference'],
                $hash['card_validity'],
                $hash['score_value'],
                $hash['score_color'],
                $hash['score_info'],
                $hash['score_threshold'],
                $hash['score_profile']
                ) = $sips_response;

        // Formatage du retour
        return array(
            'command' => $command,
            'output' => $sips_response,
            'atos_server_ip_adresses' => $this->getAtosServerIpAddresses(),
            'hash' => $hash
        );
    }

    /**
     *  Return Atos payment server IP addresses
     *
     *  @return array
     */
    public function getAtosServerIpAddresses()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }

        return explode(',', $ip);
    }

    public function describeResponse($response, $return = 'string')
    {
        $array = array();

        $string = __('Transaction number: %1', $response['transaction_id']) . "<br />";

        if (isset($response['capture_mode']) && strlen($response['capture_mode']) > 0)
            $string.= __('Mode de capture : %1', $response['capture_mode']) . "<br />";

        if (isset($response['capture_day']) && is_numeric($response['capture_day'])) {
            if ($response['capture_day'] == 0) {
                $string.= __('Day before capture: immediate capture') . "<br />";
            } else {
                $string.= __('Day before capture: %1', $response['capture_day']) . "<br />";
            }
        }

        $string.= __('Card type: %1', $response['payment_means']) . "<br />";

        // Credit card number
        if (isset($response['card_number']) && !empty($response['card_number'])) {
            $cc = explode('.', $response['card_number']);
            $array['card_number'] = $cc[0] . ' #### #### ##' . $cc[1];

            $string.= __('Card number: %1', $array['card_number']) . "<br />";
        }

        if (isset($response['cvv_flag'])) {
            switch ($response['cvv_flag']) {
                case '1':
                    switch ($response['cvv_response_code']) {
                        case '4E':
                            $array['cvv_response_code'] = __('Incorrect control number');
                            break;
                        case '4D':
                            $array['cvv_response_code'] = __('Correct control number');
                            break;
                        case '50':
                            $array['cvv_response_code'] = __('Untreated control number');
                            break;
                        case '53':
                            $array['cvv_response_code'] = __('The control number is absent in the authorization request');
                            break;
                        case '55':
                            $array['cvv_response_code'] = __('The user\'s bank is not certified, the control was not able to be made');
                            break;
                        case 'NO':
                            $array['cvv_response_code'] = __('No cryptogram on the card');
                            break;
                        default:
                            $array['cvv_response_code'] = __('No information about the cryptogram of the card');
                            break;
                    }

                    $string .= __('About the cryptogram of the card: %1', '[' . $response['cvv_response_code'] . '] ' . $array['cvv_response_code']) . "<br />";

                    if (isset($response['cvv_key'])) {
                        $array['cvv_key'] = $response['cvv_key'];
                        $string .= __('Cryptogram of the card: %1', $response['cvv_key']) . "<br />";
                    }
                    break;
            }
        }

        if (isset($response['response_code'])) {
            switch ($response['response_code']) {
                case '00':
                    $array['response_code'] = __('Accepted authorization');
                    break;
                case '02':
                    $array['response_code'] = __('Authorization request by telephone at the bank because of the ceiling of authorization on the card is exceeded');
                    break;
                case '03':
                    $array['response_code'] = __('Field merchant_id is invalid, verify the value in the request or non-existent remote sale contract, contact your bank');
                    break;
                case '05':
                    $array['response_code'] = __('Refused authorization');
                    break;
                case '12':
                    $array['response_code'] = __('Invalid transaction, verify the parameters transferred in the request');
                    break;
                case '17':
                    $array['response_code'] = __('Canceled by user');
                    break;
                case '30':
                    $array['response_code'] = __('Format error');
                    break;
                case '34':
                    $array['response_code'] = __('Fraud suspicion');
                    break;
                case '75':
                    $array['response_code'] = __('Number of attempts of card\'s number seizure is exceeded');
                    break;
                case '90':
                    $array['response_code'] = __('Service temporarily unavailable');
                    break;
                case '94':
                    $array['response_code'] = __('Transaction already saved');
                    break;
                default:
                    $array['response_code'] = __('Rejected ATOS Transaction - invalid code %1', $response['response_code']);
            }

            $string .= __('Payment platform response: %1', '[' . $response['response_code'] . '] ' . $array['response_code']) . "<br />";
        }

        if (isset($response['bank_response_code'])) {
            if (in_array($response['payment_means'], array('CB', 'VISA', 'MASTERCARD'))) {
                switch ($response['bank_response_code']) {
                    case '00':
                        $array['bank_response_code'] = __('Transaction approved or treated with success');
                        break;
                    case '02':
                        $array['bank_response_code'] = __('Contact card issuer');
                        break;
                    case '03':
                        $array['bank_response_code'] = __('Invalid acceptor');
                        break;
                    case '04':
                        $array['bank_response_code'] = __('Keep the card');
                        break;
                    case '05':
                        $array['bank_response_code'] = __('Do not honor');
                        break;
                    case '07':
                        $array['bank_response_code'] = __('Keep the card, special conditions');
                        break;
                    case '08':
                        $array['bank_response_code'] = __('Approve after identification');
                        break;
                    case '12':
                        $array['bank_response_code'] = __('Invalid transaction');
                        break;
                    case '13':
                        $array['bank_response_code'] = __('Invalid amount');
                        break;
                    case '14':
                        $array['bank_response_code'] = __('Invalid carrier number');
                        break;
                    case '15':
                        $array['bank_response_code'] = __('Unknown card issuer');
                        break;
                    case '30':
                        $array['bank_response_code'] = __('Format error');
                        break;
                    case '31':
                        $array['bank_response_code'] = __('Unknown buyer body identifier');
                        break;
                    case '33':
                    case '54':
                        $array['bank_response_code'] = __('Card validity date exceeded');
                        break;
                    case '34':
                    case '59':
                        $array['bank_response_code'] = __('Fraud suspicion');
                        break;
                    case '41':
                        $array['bank_response_code'] = __('Lost card');
                        break;
                    case '43':
                        $array['bank_response_code'] = __('Stolen card');
                        break;
                    case '51':
                        $array['bank_response_code'] = __('Insufficient reserve or exceeded credit');
                        break;
                    case '56':
                        $array['bank_response_code'] = __('Card absent in the file');
                        break;
                    case '57':
                        $array['bank_response_code'] = __('Transaction not allowed to this carrier');
                        break;
                    case '58':
                        $array['bank_response_code'] = __('Transaction forbidden to terminal');
                        break;
                    case '60':
                        $array['bank_response_code'] = __('The card acceptor has to contact the buyer');
                        break;
                    case '61':
                        $array['bank_response_code'] = __('Exceed the limit of the retreat amount');
                        break;
                    case '63':
                        $array['bank_response_code'] = __('Safety rules not respected');
                        break;
                    case '68':
                        $array['bank_response_code'] = __('Response not reached or received too late');
                        break;
                    case '90':
                        $array['bank_response_code'] = __('System Temporary Stoppage');
                        break;
                    case '91':
                        $array['bank_response_code'] = __("Inaccessible card issuer");
                        break;
                    case '96':
                        $array['bank_response_code'] = __('System malfunction');
                        break;
                    case '97':
                        $array['bank_response_code'] = __('Term of the time-lag of global surveillance');
                        break;
                    case '98':
                        $array['bank_response_code'] = __('Server unavailable, network routing asked again');
                        break;
                    case '99':
                        $array['bank_response_code'] = __('Initiator domain incident');
                        break;
                }

                if (isset($array['bank_response_code'])) {
                    $string .= __('Bank response: %1', '[' . $response['bank_response_code'] . '] ' . $array['bank_response_code']) . "<br />";
                }
            }
        }

        if (isset($response['complementary_code'])) {
            switch ($response['complementary_code']) {
                case '00':
                    $array['complementary_code'] = __('All the controls to which you subscribed were made successfully');
                    break;
                case '02':
                    $array['complementary_code'] = __('The used card exceeded the authorized outstanding');
                    break;
                case '03':
                    $array['complementary_code'] = __('The used card belongs to the merchant\'s grey list');
                    break;
                case '05':
                    $array['complementary_code'] = __('The BIN of the used card belongs to a range not referenced in the table of BIN of the MERCANET platform');
                    break;
                case '06':
                    $array['complementary_code'] = __('The card number is not in a range of the same nationality as that of the merchant');
                    break;
                case '99':
                    $array['complementary_code'] = __('The MERCANET server encountered a problem during the processing of one of the additional local controls');
                    break;
            }

            if (isset($array['complementary_code'])) {
                $string .= __('Additional control: %1', '[' . $response['complementary_code'] . '] ' . $array['complementary_code']) . "<br />";
            }
        }

        if (isset($response['data'])) {
            $array['data'] = $response['data'];
            $string .= __('Other data: %1', $response['data']) . "<br />";
        }

        if ($return == 'string') {
            return $string;
        } else {
            return $array;
        }
    }

}
