-- ============================================================================
-- Parser V4 Tables - Clean Schema (NO V3 legacy)
-- ============================================================================
-- This creates ONLY the tables needed for V4 Incremental Constructional Parser
--
-- V4 Architecture:
-- - Uses parser_construction_v4 for ALL constructions (MWE, phrasal, clausal, sentential)
-- - NO separate parser_mwe, parser_grammar_node, parser_grammar_link tables
-- - Unified construction registry with BNF patterns and priority-based resolution
--
-- For MySQL Workbench:
-- 1. Open this file in MySQL Workbench
-- 2. Execute the script
-- 3. Database → Reverse Engineer to update your model
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Core Grammar Configuration
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS parser_grammar_graph (
    idGrammarGraph INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    language VARCHAR(10) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Grammar configurations for different languages';

-- ----------------------------------------------------------------------------
-- V4 Unified Construction Registry (replaces parser_mwe, parser_grammar_node)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS parser_construction_v4 (
    idConstruction BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,

    -- Identification
    name VARCHAR(100) NOT NULL,
    constructionType VARCHAR(20) NOT NULL COMMENT 'mwe, phrasal, clausal, sentential',

    -- Pattern definition (BNF-like)
    pattern TEXT NOT NULL COMMENT 'e.g., "café" "da" "manhã" or {NOUN} {VERB}',
    compiledPattern JSON NULL COMMENT 'Pre-compiled BNF graph for fast matching',

    -- Priority-based resolution (100-199=MWE, 50-99=Phrasal, 20-49=Clausal, 1-19=Sentential)
    priority SMALLINT NOT NULL DEFAULT 50,
    enabled BOOLEAN NOT NULL DEFAULT TRUE,

    -- Multi-level CE labels
    phrasalCE VARCHAR(20) NULL COMMENT 'Head, Mod, Adp, Lnk, Clf, Idx, Conj',
    clausalCE VARCHAR(20) NULL COMMENT 'Pred, Arg, CPP, Gen, FPM, Conj',
    sententialCE VARCHAR(20) NULL COMMENT 'Main, Adv, Rel, Comp, Dtch, Int',

    -- Constraints (feature agreement, POS constraints)
    constraints JSON NOT NULL DEFAULT '[]',

    -- MWE aggregation
    aggregateAs VARCHAR(255) NULL COMMENT 'How to label aggregated MWE node',
    semanticType VARCHAR(20) NULL COMMENT 'Semantic category of aggregated node',
    semantics JSON NULL,

    -- MWE Lookahead (for ambiguous boundaries)
    lookaheadEnabled BOOLEAN NOT NULL DEFAULT FALSE,
    lookaheadMaxDistance SMALLINT NOT NULL DEFAULT 2,
    invalidationPatterns JSON NOT NULL DEFAULT '[]',
    confirmationPatterns JSON NOT NULL DEFAULT '[]',

    -- Metadata
    description TEXT NULL,
    examples JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes
    UNIQUE KEY uq_construction_name (idGrammarGraph, name),
    INDEX idx_construction_type (constructionType),
    INDEX idx_construction_priority (priority),
    INDEX idx_construction_enabled (enabled),
    INDEX idx_construction_phrasal (phrasalCE),
    INDEX idx_construction_clausal (clausalCE),
    INDEX idx_construction_sentential (sententialCE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='V4 Unified construction registry - replaces parser_mwe and parser_grammar_node';

-- ----------------------------------------------------------------------------
-- Parse Runtime Tables (instances of parsing)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS parser_graph (
    idParserGraph INT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,
    sentence TEXT NOT NULL,
    status ENUM('parsing', 'complete', 'failed') DEFAULT 'parsing',
    errorMessage TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_grammar (idGrammarGraph)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual parse instances (one per sentence)';

CREATE TABLE IF NOT EXISTS parser_node (
    idParserNode INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,

    -- Node identification
    label VARCHAR(255) NOT NULL,
    idLemma INT NULL COMMENT 'Reference to lemma table',
    pos VARCHAR(20) NULL COMMENT 'POS tag from UD',
    type ENUM('E', 'R', 'A', 'F', 'MWE') NOT NULL,

    -- Activation tracking
    threshold INT DEFAULT 1,
    activation INT DEFAULT 1,
    isFocus BOOLEAN DEFAULT FALSE,
    positionInSentence INT NOT NULL,

    -- Features from UD parser
    features JSON NULL COMMENT 'Morphological features from UD',
    stage VARCHAR(50) NULL COMMENT 'transcription, translation, folding, v4',

    -- V4 Multi-level CE labels
    phrasalCE VARCHAR(20) NULL COMMENT 'Phrasal level CE: Head, Mod, Adp, Lnk, Clf, Idx, Conj',
    clausalCE VARCHAR(20) NULL COMMENT 'Clausal level CE: Pred, Arg, CPP, Gen, FPM, Conj',
    sententialCE VARCHAR(20) NULL COMMENT 'Sentential level CE: Main, Adv, Rel, Comp, Dtch, Int',

    -- Construction tracking
    constructionName VARCHAR(100) NULL COMMENT 'Construction that created this node',
    constructionId BIGINT NULL COMMENT 'Reference to parser_construction_v4',

    -- MWE reference (for aggregated MWE nodes)
    idMWE INT NULL COMMENT 'Legacy MWE reference (V3 compatibility)',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_position (idParserGraph, positionInSentence),
    INDEX idx_focus (idParserGraph, isFocus),
    INDEX idx_node_phrasal_ce (idParserGraph, phrasalCE),
    INDEX idx_node_clausal_ce (idParserGraph, clausalCE),
    INDEX idx_node_sentential_ce (idParserGraph, sententialCE),
    INDEX idx_node_construction (constructionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parse nodes (words and aggregated MWEs) with multi-level CE labels';

CREATE TABLE IF NOT EXISTS parser_link (
    idParserLink INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,
    idSourceNode INT NOT NULL,
    idTargetNode INT NOT NULL,

    -- Link metadata
    linkType ENUM('sequential', 'activate', 'dependency', 'prediction') DEFAULT 'dependency',
    relation VARCHAR(50) NULL COMMENT 'UD relation label (nsubj, obj, etc.)',
    weight DECIMAL(3,2) DEFAULT 1.0,
    stage VARCHAR(50) NULL COMMENT 'transcription, translation, folding, v4',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    FOREIGN KEY (idSourceNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE,
    FOREIGN KEY (idTargetNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE,
    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_source (idParserGraph, idSourceNode),
    INDEX idx_target (idParserGraph, idTargetNode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dependency and structural links between parse nodes';

CREATE TABLE IF NOT EXISTS parser_alternative_log (
    idAlternativeLog BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,

    -- Alternative identification
    constructionName VARCHAR(100) NOT NULL,
    constructionType VARCHAR(20) NOT NULL,
    constructionId BIGINT NULL,

    -- Alternative state
    status VARCHAR(20) NOT NULL COMMENT 'pending, progressing, complete, confirmed, abandoned',
    activation INT NOT NULL DEFAULT 0,
    threshold INT NOT NULL DEFAULT 1,

    -- Position tracking
    startPosition INT NOT NULL,
    endPosition INT NULL,

    -- Matched components
    matchedComponents JSON NULL COMMENT 'Array of matched token data',

    -- MWE lookahead tracking
    lookaheadCounter INT NULL,
    lookaheadMaxDistance INT NULL,
    invalidationReason VARCHAR(255) NULL,

    -- Metadata
    createdAt TIMESTAMP NOT NULL,
    completedAt TIMESTAMP NULL,

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    INDEX idx_alternative_graph (idParserGraph),
    INDEX idx_alternative_status (status),
    INDEX idx_alternative_construction (constructionId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='V4 debugging log - tracks alternative lifecycle during incremental parsing';

-- ============================================================================
-- Sample Data: Minimal Portuguese Grammar
-- ============================================================================

-- Insert minimal Portuguese grammar graph for testing
INSERT IGNORE INTO parser_grammar_graph (idGrammarGraph, name, language, description) VALUES
(1, 'Portuguese V4 Grammar', 'pt', 'Portuguese grammar for V4 incremental constructional parser');

-- ============================================================================
-- V4 Architecture Summary
-- ============================================================================
--
-- Tables Used by V4:
-- 1. parser_grammar_graph - Grammar configurations
-- 2. parser_construction_v4 - Unified construction registry (MWE + phrasal + clausal + sentential)
-- 3. parser_graph - Parse instances
-- 4. parser_node - Parse nodes with multi-level CE labels
-- 5. parser_link - Dependency links
-- 6. parser_alternative_log - Debugging/analysis log
--
-- Tables NOT used by V4 (V3 legacy - can be dropped):
-- - parser_mwe (replaced by parser_construction_v4 with constructionType='mwe')
-- - parser_grammar_node (replaced by parser_construction_v4)
-- - parser_grammar_link (V4 uses construction patterns instead)
--
-- ============================================================================
