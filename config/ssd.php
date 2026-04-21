<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Graph Engine Defaults
    |--------------------------------------------------------------------------
    | Default hyperparameters used when creating a new graph and as fallback
    | values in GraphEngine when a stored config key is absent.
    */
    'graph' => [
        'defaults' => [
            'decay_factor' => 0.99,
            'reinforcement_amount' => 1.0,
            'initial_weight' => 1.0,
            'prune_threshold' => 0.01,
            'activation_propagation_factor' => 0.5,
            'within_reinforcement_multiplier' => 1.2,
            'between_reinforcement_multiplier' => 1.0,
            'within_decay_factor' => 0.995,
            'between_decay_factor' => 0.99,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre-Parser Sentence Selection
    |--------------------------------------------------------------------------
    | Controls which sentences are fetched from the webtool database when
    | running ssd:pre-parser-sentence.
    */
    'pre_parser' => [
        'corpus_id_min' => 204,
        'corpus_id_max' => 217,
        'origin_mm_ids' => [15, 16],
        'default_language' => 1,
        'default_grammar' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Competitive Construction Discovery
    |--------------------------------------------------------------------------
    */
    'competition' => [
        'alpha' => 0.1,
        'beta' => 0.05,
        'base_threshold' => 0.1,
        'annealing_rate' => 0.02,
        'epsilon' => 0.001,
        'max_iterations' => 100,
        'min_coalition_size' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Graph Visualization
    |--------------------------------------------------------------------------
    */
    'visualization' => [
        'default_format' => 'png',
        'default_layout' => 'dot',

        'colors' => [
            'within' => '#C3E6CB',
            'between' => '#F4CCCC',
            'default_relation' => '#E8D5F5',
            'higher_order_link' => '#8E44AD',
            'transition_edge' => '#2E86C1',
            'boundary_node' => '#E8E8E8',
            'default_element' => '#AEC6CF',
            'construction_cluster' => '#999999',
        ],

        'edge' => [
            'min_penwidth' => 0.5,
            'max_penwidth' => 5.0,
            'min_opacity' => 0x30,
            'max_opacity' => 0xFF,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Functional Categorization
    |--------------------------------------------------------------------------
    | Default category mapping applied by ssd:categorize --defaults, and the
    | list of relation types considered "within" (vs. "between") categories.
    */
    'categorization' => [
        'defaults' => [
            'referent' => [
                'color' => '#AEC6CF',
                'description' => 'Entities introduced into discourse',
                'elements' => ['NOUN', 'PPER', 'PINF', 'SYM'],
            ],
            'modifier' => [
                'color' => '#C3E6CB',
                'description' => 'Elements that characterize referents or predicators',
                'elements' => ['ADJ', 'DET', 'POSS', 'NUM', 'ART', 'CPP'],
            ],
            'predicator' => [
                'color' => '#F4CCCC',
                'description' => 'Elements that assert something about entities',
                'elements' => ['VERB', 'FIN', 'INF', 'PART', 'GER'],
            ],
            'functional' => [
                'color' => '#FCE4B5',
                'description' => 'Structural elements that mark relationships',
                'elements' => ['PREP', 'CCONJ', 'SCONJ'],
            ],
        ],

        'within_types' => [
            'mod→ref', 'mod→mod', 'func→ref', 'func→mod', 'mod→pred', 'pred→pred',
        ],
    ],

];
