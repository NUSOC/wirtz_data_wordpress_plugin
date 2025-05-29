<?php



/**
 * NLP Class for natural language processing functionality using Ollama local LLM
 *
 * @package StackWirtz_wordpress_plugin
 */

namespace StackWirtz\WordpressPlugin;

use Symfony\Contracts\HttpClient\HttpClientInterface;



use StackWirtz\WordpressPlugin\Models\WirtzData;


/**
 * NLP class provides natural language processing capabilities using Ollama
 */
class NLP
{
    /**
     * @var string Ollama API endpoint
     */
    private $apiEndpoint;

    /**
     * @var string Ollama model name
     */
    private $model;

    /**
     * @var string Preamble text to prepend to queries
     */
    private $queryPreamble;

    private $wirtz_data;
    private HttpClientInterface $client;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize Ollama settings
        $this->apiEndpoint = get_option('ollama_api_endpoint', 'http://localhost:11434/api/generate');
        $this->model = get_option('ollama_model', 'llama3'); // Default model, can be changed


        // get data
        $this->wirtz_data = (new WirtzData())->getData();
        $this->client = new \Symfony\Component\HttpClient\CurlHttpClient();



        $this->queryPreamble = <<<PREAMBLE
            You were only concerned with pulling the following pieces of information: 
                FIRST NAME, 
                LAST NAME, 
                NAME OF A STAGE PLAY, 
                GRADUATION YEAR, 
                ACTOR ROLE, 
                YEAR THE PLAY WAS PRODUCED,  
            Format all output in a JSON object with the following keys: first_name, last_name, stage_play, graduation_year, actor_role, run_year
            If you do not know the value of a field, use "unknown" as the value. Do not include explanatory text in your responses.
            Play run_year will usually be in school years such as 2020-2021
            Do not try to actually answer the question we are mainly concerned with producing this JSON object.
            You only want to pass the sentence below no other information matters.
         PREAMBLE;
    }

    /**
     * Process text through Ollama API for natural language processing
     *
     * @param string $text The input text to be processed
     * @return array Associative array containing:
     *               - processed_text: The processed response from Ollama
     *               - status: 'success' or 'error'
     *               - error: Error message if status is 'error'
     */
    public function processText($text)
    {
        if (empty($text)) {
            return [
                'processed_text' => '',
                'status' => 'error',
                'error' => 'Empty input text'
            ];
        }

        $data = [
            'model' => $this->model,
            'prompt' => $this->queryPreamble . $text,
            'stream' => false
        ];

        try {
            $response = $this->client->request('POST', $this->apiEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);

            $result = $response->toArray();

            if (isset($result['response'])) {
                return [
                    'processed_text' => $result['response'],
                    'status' => 'success'
                ];
            }
            return [
                'processed_text' => '',
                'status' => 'error',
                'error' => 'Invalid JSON response: ' . json_last_error_msg()
            ];
        } catch (\Exception $e) {
            return [
                'processed_text' => '',
                'status' => 'error',
                'error' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Display NLP interface
     *
     * @return string Rendered template
     */
    public function displayNLP()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);


        // Sanitize all POST data
        $posts = array_map(function ($value) {
            if (is_array($value)) {
                return array_map('sanitize_text_field', $value);
            }
            return sanitize_text_field($value);
        }, $_POST);

        if (!empty($posts['query'])) {
            $question = $posts['query'];
            $result = $this->processText($question);
            $result['processed_text'] = json_decode($result['processed_text'], true);
            dump($result);
        }



        return $twig->render('nlp.html.twig', [
            'results' => $result ?? null,
            'question' => $question ?? null,
            'process_text' => $result['processed_text'] ?? null,

            'first' => $result['processed_text']['first_name'] ?? null,
            'last' => $result['processed_text']['last_name'] ?? null,
            'production' => $result['processed_text']['stage_play'] ?? null,
            'graduation_year' => $result['processed_text']['graduation_year'] ?? null,
            'actor_role' => $result['processed_text']['actor_role'] ?? null,
            'run_year' => $result['processed_text']['run_year'] ?? null,


            'returnPage' => $currentUrl = $_SERVER['REQUEST_URI'],
        ]);
    }
}
