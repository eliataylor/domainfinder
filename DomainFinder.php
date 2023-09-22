<?php

include('vendor/autoload.php');
include '../phpWhois.org/src/whois.main.php';

// https://github.com/DaveChild/Text-Statistics
use \DaveChild\TextStatistics as TS;
use Helge\Loader\JsonLoader;
use Helge\Client\SimpleWhoisClient;
use Helge\Service\DomainAvailability;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

class DomainFinder
{

    // https://whois.whoisxmlapi.com/

    private $taken = [];
    private $avail = [];
    private $whois = [];
    private $skipped = [];

    // private $tdls = ['.com', '.app']; // .team, .farm, .casino, .cash, .studio
    // private $tdls = ['.com'];
    // private $tdls = ['.pro'];
    // private $tdls = ['.team'];
    // private $tdls = ['.com', '.app', '.club', '.community', '.pro', '.team', '.training'];
    private $tdls = ['.com', '.app', '.club', '.pro', '.team'];
    private $prefixes = ['we', 'i', 'u', 'you']; // 're', 'ex'
    // private $suffixes = ['OS', 's', 'UP', 'US'];
    // private $suffixes = ['s', 'able', 'ly', 'ish', 'ing', 'ness'];
    private $suffixes = [];

    private $nouns = ['music', 'voice', 'song', 'track', 'trackcash', 'anthem', 'playlist', 'jam', 'sound', 'mix', 'record', 'talent'];
    private $verbs = ['rise', 'royalty', 'pop', 'shake', 'move', 'slam', 'slide', 'skate', 'live', 'vibe', 'vybe', 'pulse', 'listen', 'mix', 'play', 'got'];
    private $adjs = ['great', 'super'];
    private $monos = 'life, love, ER, world, ME, one, Day, AL, you, IN, tip, heart, on, Ate, no, Be, to, ay, near, ion, ness, ring, ace, 
    wolf, go, re, five, man, el, star, ten, DO, mouth, soul, rich, age, foot, ex, lion, red, and, 
    live, dream, key, it, pain, own, laugh, rain, once, bo, ball, with, fire, he, fa, six, wood, care, can, sun, cake, back, faith, ers, mi';

    /* @var Whois */
    private $whois_client;

    function __construct() {
        $this->whois_client = new Whois();
        $this->whois_client->non_icann = true;
        $this->whois_client->deep_whois = true;

        $whoisClient = new SimpleWhoisClient();
        $dataLoader = new JsonLoader(__DIR__."/vendor/helgesverre/domain-availability/src/data/servers.json");
        $this->whois_service = new DomainAvailability($whoisClient, $dataLoader);


        //$this->prefixes = str_split('abcdefghijklmnopqrstuvwxyz');
        //$this->prefixes = str_split('iua'); // iaeou
        //$this->prefixes = array_merge($this->prefixes, ['we', 'you', 'your', 'my', 'our', 're', 'ex']); // 'Da'
    }


    public function domainWhois($domain)
    {
        $results = $this->whois_client->Lookup($domain,false);
        return $results;
    }


    public function domainAvailable($domain): bool
    {
        try {
            $test = $this->whois_service->isAvailable($domain);
        } catch (Exception $e) {
            try {
                $test = $this->whois_service->isAvailable($domain, true);
            } catch (Exception $e) {
                $test = @dns_get_record($domain, DNS_ANY);
                $test = empty($test);
            }
        }

        return $test;
        /*
        $results = @dns_get_record($domain, DNS_ANY);
        if (empty($results)) {
            return true; // available
        }
        return false;
        */
    }

    private function storeResult($avail, $pre, $domain, $suf, $tdl) {
        $val = [$pre, ucfirst($domain), $suf];
        $url = implode('', $val);
        $syllables = \DaveChild\TextStatistics\Syllables::syllableCount($url);
        $url .= $tdl;
        $val[] = $tdl;
        $val[] = $syllables;

        if (!is_bool($avail)) {
            unset($avail['rawdata']);
            $avail['syllables'] = $syllables;
            $avail['domain'] = $url;
            if(empty($avail["regyinfo"])) {
                $avail['available'] = 'isAvailable';
            } else {
                $avail['available'] = 'isNotAvailable';
            }
            $avail['errstr'] = $this->whois_client->Query['errstr'];

            $this->whois[] = $avail;
        } else if ($avail === true) {
            $this->avail[$url] = $val;
        } else {
            $this->taken[$url] = $val;
        }
    }

    public function checkNouns() {
        $this->monos = explode(', ', $this->monos);
        foreach($this->monos as $noun) {
            if (strlen(trim($noun)) < 4) continue;
            $this->checkVariations($noun);
        }
    }


    public function checkVariations($domain)
    {
        $domain = trim($domain);
        foreach ($this->tdls as $tdl) {
            $test = $this->domainAvailable($domain . $tdl);
            $this->storeResult($test, '', $domain, '', $tdl);
            foreach($this->prefixes as $pre) {
              $test = $this->domainAvailable($pre.$domain.$tdl);
              $this->storeResult($test, $pre, $domain, '', $tdl);
              foreach($this->suffixes as $suf) {
                $test = $this->domainAvailable($pre.$domain.$suf.$tdl);
                $this->storeResult($test, $pre, $domain, $suf, $tdl);
              }
            }
            foreach($this->suffixes as $suf) {
              $test = $this->domainAvailable($domain.$suf.$tdl);
              $this->storeResult($test, '', $domain, $suf, $tdl);
            }
        }
    }

    public function checkWhoisVariations($domain)
    {
        $domain = trim($domain);
        foreach ($this->tdls as $tdl) {
            $test = $this->domainWhois($domain . $tdl);
            $this->storeResult($test, '', $domain, '', $tdl);
            foreach($this->prefixes as $pre) {
                $test = $this->domainWhois($pre.$domain.$tdl);
                $this->storeResult($test, $pre, $domain, '', $tdl);
                foreach($this->suffixes as $suf) {
                    $test = $this->domainWhois($pre.$domain.$suf.$tdl);
                    $this->storeResult($test, $pre, $domain, $suf, $tdl);
                }
            }
            foreach($this->suffixes as $suf) {
                $test = $this->domainWhois($domain.$suf.$tdl);
                $this->storeResult($test, '', $domain, $suf, $tdl);
            }
        }
    }

    public function toJson() {
        return json_encode(['whois'=>$this->whois, 'available'=>$this->avail, 'taken'=>$this->taken], JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
    }

    public function printAvails($toJson=false) {
        if ($toJson === true) return json_encode($this->avail, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
        return $this->toHTML($this->avail);
    }

    public function printTaken($toJson=false) {
        if ($toJson === true) return json_encode($this->taken, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
        return $this->toHTML($this->taken);
    }

    private function toHTML($results)
    {
        $html = '<table class="table table-dark"><thead>
    <tr>
      <th scope="col">Prefix</th>
      <th scope="col">Domain</th>
      <th scope="col">Suffix</th>
      <th scope="col">TDL</th>
      <th scope="col">Syllabuls</th>
      <th scope="col">URL</th>
    </tr>
  </thead><tbody>';
        foreach ($results as $url=>$res) {
            $class = '';
            if (empty($res[0]) && empty($res[2])) {
                $html .= '<tr class="table-success" style="color:black">';
            } else {
                if (!empty($res[0]) && !empty($res[2])) {
                    $class = 'table-secondary';
                }
                $html .= '<tr class="' . $class . '">';
            }
            foreach ($res as $r) {
                $html .= '<td>' . $r . '</td>';
            }
            $html .= '<td>' . $url . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

}
