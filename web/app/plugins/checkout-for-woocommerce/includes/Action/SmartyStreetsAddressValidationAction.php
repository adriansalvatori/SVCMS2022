<?php

namespace Objectiv\Plugins\Checkout\Action;

use SmartyStreets\PhpSdk\ClientBuilder;
use SmartyStreets\PhpSdk\International_Street\Client as InternationalStreetApiClient;
use SmartyStreets\PhpSdk\International_Street\Lookup;
use SmartyStreets\PhpSdk\StaticCredentials;
use SmartyStreets\PhpSdk\US_Street\Client as USStreetApiClient;

/**
 * Class SmartyStreetsAddressValidationAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class SmartyStreetsAddressValidationAction extends CFWAction {
	protected $smartystreets_auth_id;
	protected $smartystreets_auth_token;

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $smartystreets_auth_id, $smartystreets_auth_token ) {
		parent::__construct( 'cfw_smartystreets_address_validation' );

		$this->smartystreets_auth_id    = $smartystreets_auth_id;
		$this->smartystreets_auth_token = $smartystreets_auth_token;
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		try {
			$original_address = $_POST['address'];

			if ( ! is_array( $original_address ) ) {
				throw new \Exception( 'POST address is not a valid array of address info.' );
			}

			$suggested_address = $this->get_suggested_address( $original_address );

			$output_address = $this->format_suggested_address( $original_address, $suggested_address );

			$this->out(
				array(
					'result'     => true,
					'address'    => stripslashes( $output_address ),
					'original'   => stripslashes( WC()->countries->get_formatted_address( $original_address ) ),
					'components' => $suggested_address,
				)
			);
		} catch ( \Exception $ex ) {
			$this->out(
				array(
					'result'  => false,
					'message' => $ex->getMessage(),
				)
			);
		}
	}

	protected function format_suggested_address( array $original_address, array $suggested_address ) {
		$changed_component_keys = array_keys( array_diff_assoc( $suggested_address, $original_address ) );

		if ( empty( $changed_component_keys ) ) {
			throw new \Exception( 'Suggested address matched input address' );
		}

		$poisoned_address = $suggested_address;
		$replace_start    = 'checkoutwc_0';
		$replace_end      = 'checkoutwc_1';

		foreach ( $changed_component_keys as $key ) {
			$poisoned_address[ $key ] = "{$replace_start}{$suggested_address[$key]}{$replace_end}";
		}

		$output_address = WC()->countries->get_formatted_address( $poisoned_address );
		$output_address = str_ireplace( $replace_start, '<span style="color:red">', $output_address );
		$output_address = str_ireplace( $replace_end, '</span>', $output_address );

		return $output_address;
	}

	protected function get_suggested_address( array $address ) {
		$credentials = new StaticCredentials( $this->smartystreets_auth_id, $this->smartystreets_auth_token );
		$builder     = new ClientBuilder( $credentials );

		$builder->retryAtMost( 0 )->withMaxTimeout( 3000 );

		// Whenever we add another condition to this tree it's time to break this out into OO with factory.
		if ( 'US' === $address['country'] ) {
			return $this->getDomesticAddressSuggestion( $address, $builder->buildUsStreetApiClient() );
		} elseif ( 'GB' === $address['country'] ) {
			return $this->getUKAddressSuggestion( $address, $builder->buildInternationalStreetApiClient() );
		} else {
			return $this->getInternationalAddressSuggestion( $address, $builder->buildInternationalStreetApiClient() );
		}
	}

	/**
	 * @param array $address
	 * @param USStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getDomesticAddressSuggestion( array $address, USStreetApiClient $client ): array {
		$lookup = new \SmartyStreets\PhpSdk\US_Street\Lookup();

		$lookup->setStreet( $address['address_1'] );
		$lookup->setStreet2( $address['address_2'] );
		$lookup->setCity( $address['city'] );
		$lookup->setState( $address['state'] );
		$lookup->setZipcode( $address['postcode'] );
		$lookup->setMaxCandidates( 1 );
		$lookup->setMatchStrategy( 'invalid' );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\US_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];

		$suggested_address   = $first_candidate->getDeliveryLine1();
		$suggested_address_2 = $first_candidate->getDeliveryLine2();
		$suggested_postcode  = $first_candidate->getComponents()->getZipcode();
		$suggested_state     = $first_candidate->getComponents()->getStateAbbreviation();
		$suggested_city      = $first_candidate->getComponents()->getCityName();

		return array(
			'address_1' => $suggested_address,
			'address_2' => $suggested_address_2,
			'city'      => $suggested_city,
			'state'     => $suggested_state,
			'postcode'  => $suggested_postcode,
			'country'   => 'US',
			'company'   => $address['company'],
		);
	}

	/**
	 * @param array $address
	 * @param InternationalStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getInternationalAddressSuggestion( array $address, InternationalStreetApiClient $client ): array {
		$lookup = new Lookup();

		$lookup->setInputId( '0' );
		$lookup->setAddress1( $address['address_1'] );
		$lookup->setAddress2( $address['address_2'] );
		$lookup->setLocality( $address['city'] );
		$lookup->setAdministrativeArea( $address['state'] );
		$lookup->setCountry( $address['country'] );
		$lookup->setPostalCode( $address['postcode'] );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\International_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];
		$analysis        = $first_candidate->getAnalysis();
		$precision       = $analysis->getAddressPrecision();

		if ( 'Premise' !== $precision && 'DeliveryPoint' !== $precision ) {
			throw new \Exception( 'Candidate match is too fuzzy' );
		}

		$suggested_address   = $first_candidate->getAddress1();
		$suggested_address_2 = $first_candidate->getAddress2();
		$iso3                = $first_candidate->getComponents()->getCountryIso3();
		$suggested_country   = $this->get_iso_2( $iso3 );
		$postcode_extra      = $first_candidate->getComponents()->getPostalCodeExtra();
		$postcode_suffix     = empty( $postcode_extra ) ? '' : ' - ' . $postcode_extra;
		$postcode_short      = $first_candidate->getComponents()->getPostalCodeShort();
		$suggested_zip       = $postcode_short . $postcode_suffix;
		$suggested_state     = $first_candidate->getComponents()->getAdministrativeArea();
		$suggested_city      = $first_candidate->getComponents()->getLocality();

		return array(
			'house_number' => $first_candidate->getComponents()->getPremise(),
			'street_name'  => $first_candidate->getComponents()->getThoroughfare(),
			'address_1'    => $suggested_address,
			'address_2'    => $suggested_address_2,
			'company'      => $address['company'],
			'city'         => $suggested_city,
			'country'      => $suggested_country,
			'state'        => $suggested_state,
			'postcode'     => $suggested_zip,
		);
	}

		/**
	 * @param array $address
	 * @param InternationalStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	public function getUKAddressSuggestion( array $address, InternationalStreetApiClient $client ): array {
		$lookup = new Lookup();

		$lookup->setInputId( '0' );
		$lookup->setAddress1( $address['address_1'] );
		$lookup->setAddress2( $address['address_2'] );
		$lookup->setLocality( $address['city'] );
		$lookup->setAdministrativeArea( $address['state'] );
		$lookup->setCountry( $address['country'] );
		$lookup->setPostalCode( $address['postcode'] );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\International_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];
		$analysis        = $first_candidate->getAnalysis();
		$precision       = $analysis->getAddressPrecision();

		if ( 'Premise' !== $precision && 'DeliveryPoint' !== $precision ) {
			throw new \Exception( 'Candidate match is too fuzzy' );
		}

		$suggested_address   = $first_candidate->getAddress1();
		$suggested_address_2 = $first_candidate->getAddress2();
		$iso3                = $first_candidate->getComponents()->getCountryIso3();
		$suggested_country   = $this->get_iso_2( $iso3 );
		$postcode_extra      = $first_candidate->getComponents()->getPostalCodeExtra();
		$postcode_suffix     = empty( $postcode_extra ) ? '' : ' - ' . $postcode_extra;
		$postcode_short      = $first_candidate->getComponents()->getPostalCodeShort();
		$suggested_zip       = $postcode_short . $postcode_suffix;
		$suggested_state     = $first_candidate->getComponents()->getAdministrativeArea();
		$suggested_city      = $first_candidate->getComponents()->getLocality();

		$result = array(
			'house_number' => $first_candidate->getComponents()->getPremise(),
			'street_name'  => $first_candidate->getComponents()->getThoroughfare(),
			'address_1'    => $suggested_address,
			'company'      => $address['company'],
			'city'         => $suggested_city,
			'country'      => $suggested_country,
			'state'        => $suggested_state,
			'postcode'     => $suggested_zip,
		);

		if ( ! in_array( $suggested_address_2, $result, true ) ) {
			$result['address_2'] = $suggested_address_2;
		}

		return $result;
	}

	private function get_iso_2( string $iso3 ): string {
		$map = array(
			'AFG' => 'AF', //Afghanistan
			'ALB' => 'AL', //Albania
			'DZA' => 'DZ', //Algeria
			'ASM' => 'AS', //American Samoa
			'AND' => 'AD', //Andorra
			'AGO' => 'AO', //Angola
			'AIA' => 'AI', //Anguilla
			'ATA' => 'AQ', //Antarctica
			'ATG' => 'AG', //Antigua and Barbuda
			'ARG' => 'AR', //Argentina
			'ARM' => 'AM', //Armenia
			'ABW' => 'AW', //Aruba
			'AUS' => 'AU', //Australia
			'AUT' => 'AT', //Austria
			'AZE' => 'AZ', //Azerbaijan
			'BHS' => 'BS', //Bahamas (the)
			'BHR' => 'BH', //Bahrain
			'BGD' => 'BD', //Bangladesh
			'BRB' => 'BB', //Barbados
			'BLR' => 'BY', //Belarus
			'BEL' => 'BE', //Belgium
			'BLZ' => 'BZ', //Belize
			'BEN' => 'BJ', //Benin
			'BMU' => 'BM', //Bermuda
			'BTN' => 'BT', //Bhutan
			'BOL' => 'BO', //Bolivia (Plurinational State of)
			'BES' => 'BQ', //Bonaire, Sint Eustatius and Saba
			'BIH' => 'BA', //Bosnia and Herzegovina
			'BWA' => 'BW', //Botswana
			'BVT' => 'BV', //Bouvet Island
			'BRA' => 'BR', //Brazil
			'IOT' => 'IO', //British Indian Ocean Territory (the)
			'BRN' => 'BN', //Brunei Darussalam
			'BGR' => 'BG', //Bulgaria
			'BFA' => 'BF', //Burkina Faso
			'BDI' => 'BI', //Burundi
			'CPV' => 'CV', //Cabo Verde
			'KHM' => 'KH', //Cambodia
			'CMR' => 'CM', //Cameroon
			'CAN' => 'CA', //Canada
			'CYM' => 'KY', //Cayman Islands (the)
			'CAF' => 'CF', //Central African Republic (the)
			'TCD' => 'TD', //Chad
			'CHL' => 'CL', //Chile
			'CHN' => 'CN', //China
			'CXR' => 'CX', //Christmas Island
			'CCK' => 'CC', //Cocos (Keeling) Islands (the)
			'COL' => 'CO', //Colombia
			'COM' => 'KM', //Comoros (the)
			'COD' => 'CD', //Congo (the Democratic Republic of the)
			'COG' => 'CG', //Congo (the)
			'COK' => 'CK', //Cook Islands (the)
			'CRI' => 'CR', //Costa Rica
			'HRV' => 'HR', //Croatia
			'CUB' => 'CU', //Cuba
			'CUW' => 'CW', //Curaçao
			'CYP' => 'CY', //Cyprus
			'CZE' => 'CZ', //Czechia
			'CIV' => 'CI', //Côte d'Ivoire
			'DNK' => 'DK', //Denmark
			'DJI' => 'DJ', //Djibouti
			'DMA' => 'DM', //Dominica
			'DOM' => 'DO', //Dominican Republic (the)
			'ECU' => 'EC', //Ecuador
			'EGY' => 'EG', //Egypt
			'SLV' => 'SV', //El Salvador
			'GNQ' => 'GQ', //Equatorial Guinea
			'ERI' => 'ER', //Eritrea
			'EST' => 'EE', //Estonia
			'SWZ' => 'SZ', //Eswatini
			'ETH' => 'ET', //Ethiopia
			'FLK' => 'FK', //Falkland Islands (the) [Malvinas]
			'FRO' => 'FO', //Faroe Islands (the)
			'FJI' => 'FJ', //Fiji
			'FIN' => 'FI', //Finland
			'FRA' => 'FR', //France
			'GUF' => 'GF', //French Guiana
			'PYF' => 'PF', //French Polynesia
			'ATF' => 'TF', //French Southern Territories (the)
			'GAB' => 'GA', //Gabon
			'GMB' => 'GM', //Gambia (the)
			'GEO' => 'GE', //Georgia
			'DEU' => 'DE', //Germany
			'GHA' => 'GH', //Ghana
			'GIB' => 'GI', //Gibraltar
			'GRC' => 'GR', //Greece
			'GRL' => 'GL', //Greenland
			'GRD' => 'GD', //Grenada
			'GLP' => 'GP', //Guadeloupe
			'GUM' => 'GU', //Guam
			'GTM' => 'GT', //Guatemala
			'GGY' => 'GG', //Guernsey
			'GIN' => 'GN', //Guinea
			'GNB' => 'GW', //Guinea-Bissau
			'GUY' => 'GY', //Guyana
			'HTI' => 'HT', //Haiti
			'HMD' => 'HM', //Heard Island and McDonald Islands
			'VAT' => 'VA', //Holy See (the)
			'HND' => 'HN', //Honduras
			'HKG' => 'HK', //Hong Kong
			'HUN' => 'HU', //Hungary
			'ISL' => 'IS', //Iceland
			'IND' => 'IN', //India
			'IDN' => 'ID', //Indonesia
			'IRN' => 'IR', //Iran (Islamic Republic of)
			'IRQ' => 'IQ', //Iraq
			'IRL' => 'IE', //Ireland
			'IMN' => 'IM', //Isle of Man
			'ISR' => 'IL', //Israel
			'ITA' => 'IT', //Italy
			'JAM' => 'JM', //Jamaica
			'JPN' => 'JP', //Japan
			'JEY' => 'JE', //Jersey
			'JOR' => 'JO', //Jordan
			'KAZ' => 'KZ', //Kazakhstan
			'KEN' => 'KE', //Kenya
			'KIR' => 'KI', //Kiribati
			'PRK' => 'KP', //Korea (the Democratic People's Republic of)
			'KOR' => 'KR', //Korea (the Republic of)
			'KWT' => 'KW', //Kuwait
			'KGZ' => 'KG', //Kyrgyzstan
			'LAO' => 'LA', //Lao People's Democratic Republic (the)
			'LVA' => 'LV', //Latvia
			'LBN' => 'LB', //Lebanon
			'LSO' => 'LS', //Lesotho
			'LBR' => 'LR', //Liberia
			'LBY' => 'LY', //Libya
			'LIE' => 'LI', //Liechtenstein
			'LTU' => 'LT', //Lithuania
			'LUX' => 'LU', //Luxembourg
			'MAC' => 'MO', //Macao
			'MDG' => 'MG', //Madagascar
			'MWI' => 'MW', //Malawi
			'MYS' => 'MY', //Malaysia
			'MDV' => 'MV', //Maldives
			'MLI' => 'ML', //Mali
			'MLT' => 'MT', //Malta
			'MHL' => 'MH', //Marshall Islands (the)
			'MTQ' => 'MQ', //Martinique
			'MRT' => 'MR', //Mauritania
			'MUS' => 'MU', //Mauritius
			'MYT' => 'YT', //Mayotte
			'MEX' => 'MX', //Mexico
			'FSM' => 'FM', //Micronesia (Federated States of)
			'MDA' => 'MD', //Moldova (the Republic of)
			'MCO' => 'MC', //Monaco
			'MNG' => 'MN', //Mongolia
			'MNE' => 'ME', //Montenegro
			'MSR' => 'MS', //Montserrat
			'MAR' => 'MA', //Morocco
			'MOZ' => 'MZ', //Mozambique
			'MMR' => 'MM', //Myanmar
			'NAM' => 'NA', //Namibia
			'NRU' => 'NR', //Nauru
			'NPL' => 'NP', //Nepal
			'NLD' => 'NL', //Netherlands (the)
			'NCL' => 'NC', //New Caledonia
			'NZL' => 'NZ', //New Zealand
			'NIC' => 'NI', //Nicaragua
			'NER' => 'NE', //Niger (the)
			'NGA' => 'NG', //Nigeria
			'NIU' => 'NU', //Niue
			'NFK' => 'NF', //Norfolk Island
			'MNP' => 'MP', //Northern Mariana Islands (the)
			'NOR' => 'NO', //Norway
			'OMN' => 'OM', //Oman
			'PAK' => 'PK', //Pakistan
			'PLW' => 'PW', //Palau
			'PSE' => 'PS', //Palestine, State of
			'PAN' => 'PA', //Panama
			'PNG' => 'PG', //Papua New Guinea
			'PRY' => 'PY', //Paraguay
			'PER' => 'PE', //Peru
			'PHL' => 'PH', //Philippines (the)
			'PCN' => 'PN', //Pitcairn
			'POL' => 'PL', //Poland
			'PRT' => 'PT', //Portugal
			'PRI' => 'PR', //Puerto Rico
			'QAT' => 'QA', //Qatar
			'MKD' => 'MK', //Republic of North Macedonia
			'ROU' => 'RO', //Romania
			'RUS' => 'RU', //Russian Federation (the)
			'RWA' => 'RW', //Rwanda
			'REU' => 'RE', //Réunion
			'BLM' => 'BL', //Saint Barthélemy
			'SHN' => 'SH', //Saint Helena, Ascension and Tristan da Cunha
			'KNA' => 'KN', //Saint Kitts and Nevis
			'LCA' => 'LC', //Saint Lucia
			'MAF' => 'MF', //Saint Martin (French part)
			'SPM' => 'PM', //Saint Pierre and Miquelon
			'VCT' => 'VC', //Saint Vincent and the Grenadines
			'WSM' => 'WS', //Samoa
			'SMR' => 'SM', //San Marino
			'STP' => 'ST', //Sao Tome and Principe
			'SAU' => 'SA', //Saudi Arabia
			'SEN' => 'SN', //Senegal
			'SRB' => 'RS', //Serbia
			'SYC' => 'SC', //Seychelles
			'SLE' => 'SL', //Sierra Leone
			'SGP' => 'SG', //Singapore
			'SXM' => 'SX', //Sint Maarten (Dutch part)
			'SVK' => 'SK', //Slovakia
			'SVN' => 'SI', //Slovenia
			'SLB' => 'SB', //Solomon Islands
			'SOM' => 'SO', //Somalia
			'ZAF' => 'ZA', //South Africa
			'SGS' => 'GS', //South Georgia and the South Sandwich Islands
			'SSD' => 'SS', //South Sudan
			'ESP' => 'ES', //Spain
			'LKA' => 'LK', //Sri Lanka
			'SDN' => 'SD', //Sudan (the)
			'SUR' => 'SR', //Suriname
			'SJM' => 'SJ', //Svalbard and Jan Mayen
			'SWE' => 'SE', //Sweden
			'CHE' => 'CH', //Switzerland
			'SYR' => 'SY', //Syrian Arab Republic
			'TWN' => 'TW', //Taiwan (Province of China)
			'TJK' => 'TJ', //Tajikistan
			'TZA' => 'TZ', //Tanzania, United Republic of
			'THA' => 'TH', //Thailand
			'TLS' => 'TL', //Timor-Leste
			'TGO' => 'TG', //Togo
			'TKL' => 'TK', //Tokelau
			'TON' => 'TO', //Tonga
			'TTO' => 'TT', //Trinidad and Tobago
			'TUN' => 'TN', //Tunisia
			'TUR' => 'TR', //Turkey
			'TKM' => 'TM', //Turkmenistan
			'TCA' => 'TC', //Turks and Caicos Islands (the)
			'TUV' => 'TV', //Tuvalu
			'UGA' => 'UG', //Uganda
			'UKR' => 'UA', //Ukraine
			'ARE' => 'AE', //United Arab Emirates (the)
			'GBR' => 'GB', //United Kingdom of Great Britain and Northern Ireland (the)
			'UMI' => 'UM', //United States Minor Outlying Islands (the)
			'USA' => 'US', //United States of America (the)
			'URY' => 'UY', //Uruguay
			'UZB' => 'UZ', //Uzbekistan
			'VUT' => 'VU', //Vanuatu
			'VEN' => 'VE', //Venezuela (Bolivarian Republic of)
			'VNM' => 'VN', //Viet Nam
			'VGB' => 'VG', //Virgin Islands (British)
			'VIR' => 'VI', //Virgin Islands (U.S.)
			'WLF' => 'WF', //Wallis and Futuna
			'ESH' => 'EH', //Western Sahara
			'YEM' => 'YE', //Yemen
			'ZMB' => 'ZM', //Zambia
			'ZWE' => 'ZW', //Zimbabwe
			'ALA' => 'AX', //Åland Islands
		);

		return $map[ $iso3 ];
	}
}
