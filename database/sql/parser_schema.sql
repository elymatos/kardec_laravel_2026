-- ============================================================================
-- Parser Schema: Graph-Based Predictive Parser with MWE Processing
-- ============================================================================
-- This schema supports a predictive graph-based parser inspired by Relational
-- Network Theory with activation-based mechanisms for multi-word expression
-- processing and incremental sentence parsing.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Grammar Graph Tables
-- ----------------------------------------------------------------------------

-- Grammar graphs define the base grammatical rules and structure
CREATE TABLE IF NOT EXISTS parser_grammar_graph (
    idGrammarGraph INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    language VARCHAR(10) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grammar nodes represent word types (E, R, A) and fixed words (F)
CREATE TABLE IF NOT EXISTS parser_grammar_node (
    idGrammarNode INT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    type ENUM('E', 'R', 'A', 'F', 'MWE') NOT NULL,
    threshold INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE,
    INDEX idx_grammar_label (idGrammarGraph, label),
    INDEX idx_grammar_type (idGrammarGraph, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grammar links define valid transitions between nodes
CREATE TABLE IF NOT EXISTS parser_grammar_link (
    idGrammarLink INT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,
    idSourceNode INT NOT NULL,
    idTargetNode INT NOT NULL,
    linkType ENUM('sequential', 'activate', 'dependency', 'prediction') NOT NULL,
    weight DECIMAL(3,2) DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE,
    FOREIGN KEY (idSourceNode) REFERENCES parser_grammar_node(idGrammarNode) ON DELETE CASCADE,
    FOREIGN KEY (idTargetNode) REFERENCES parser_grammar_node(idGrammarNode) ON DELETE CASCADE,
    INDEX idx_grammar_source (idGrammarGraph, idSourceNode),
    INDEX idx_grammar_target (idGrammarGraph, idTargetNode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multi-word expressions with automatic prefix hierarchy generation
CREATE TABLE IF NOT EXISTS parser_mwe (
    idMWE INT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,
    phrase VARCHAR(255) NOT NULL,
    components JSON NOT NULL,
    semanticType ENUM('E', 'R', 'A', 'F') NOT NULL,
    length INT NOT NULL,
    firstWord VARCHAR(100) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(components, '$[0]'))) VIRTUAL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE,
    INDEX idx_mwe_phrase (idGrammarGraph, phrase),
    INDEX idx_mwe_first_word (idGrammarGraph, firstWord)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Parse Graph Tables (Runtime Instances)
-- ----------------------------------------------------------------------------

-- Parse graphs represent individual parsing instances
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parse nodes represent instantiated words and MWE nodes during parsing
CREATE TABLE IF NOT EXISTS parser_node (
    idParserNode INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    type ENUM('E', 'R', 'A', 'F', 'MWE') NOT NULL,
    threshold INT DEFAULT 1,
    activation INT DEFAULT 1,
    isFocus BOOLEAN DEFAULT FALSE,
    positionInSentence INT NOT NULL,
    idMWE INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    FOREIGN KEY (idMWE) REFERENCES parser_mwe(idMWE) ON DELETE SET NULL,
    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_position (idParserGraph, positionInSentence),
    INDEX idx_focus (idParserGraph, isFocus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parse links represent relationships between nodes in the parse graph
CREATE TABLE IF NOT EXISTS parser_link (
    idParserLink INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,
    idSourceNode INT NOT NULL,
    idTargetNode INT NOT NULL,
    linkType ENUM('sequential', 'activate', 'dependency', 'prediction') DEFAULT 'dependency',
    weight DECIMAL(3,2) DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    FOREIGN KEY (idSourceNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE,
    FOREIGN KEY (idTargetNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE,
    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_source (idParserGraph, idSourceNode),
    INDEX idx_target (idParserGraph, idTargetNode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Sample Data: Portuguese Grammar Graph
-- ============================================================================

-- Insert base Portuguese grammar graph
INSERT INTO parser_grammar_graph (idGrammarGraph, name, language, description) VALUES
(1, 'Portuguese Basic Grammar', 'pt', 'Basic Portuguese grammar with 4 word types (E, R, A, F) and common MWEs');

-- Insert abstract word type nodes
INSERT INTO parser_grammar_node (idGrammarNode, idGrammarGraph, label, type, threshold) VALUES
(1, 1, 'E', 'E', 1),  -- Entities (nouns, proper nouns)
(2, 1, 'R', 'R', 1),  -- Relational (verbs, actions)
(3, 1, 'A', 'A', 1),  -- Attributes (adjectives, adverbs)
(4, 1, 'F_o', 'F', 1),      -- Function word: "o" (the - masc)
(5, 1, 'F_a', 'F', 1),      -- Function word: "a" (the - fem)
(6, 1, 'F_de', 'F', 1),     -- Function word: "de" (of/from)
(7, 1, 'F_da', 'F', 1),     -- Function word: "da" (of the - fem)
(8, 1, 'F_do', 'F', 1),     -- Function word: "do" (of the - masc)
(9, 1, 'F_em', 'F', 1),     -- Function word: "em" (in)
(10, 1, 'F_que', 'F', 1),   -- Function word: "que" (that/which)
(11, 1, 'F_eu', 'F', 1),    -- Function word: "eu" (I)
(12, 1, 'F_ele', 'F', 1);   -- Function word: "ele" (he)

-- Insert basic grammar links (simplified predictions)
INSERT INTO parser_grammar_link (idGrammarGraph, idSourceNode, idTargetNode, linkType, weight) VALUES
-- R can predict E (verb -> object)
(1, 2, 1, 'prediction', 0.9),
-- R can predict A (verb -> adverb)
(1, 2, 3, 'prediction', 0.7),
-- E can predict R (subject -> verb)
(1, 1, 2, 'prediction', 0.8),
-- E can predict A (noun -> adjective)
(1, 1, 3, 'prediction', 0.6),
-- A can predict E (adjective -> noun)
(1, 3, 1, 'prediction', 0.5),
-- F can predict E (article/prep -> noun)
(1, 4, 1, 'prediction', 0.9),
(1, 5, 1, 'prediction', 0.9),
(1, 6, 1, 'prediction', 0.8),
(1, 7, 1, 'prediction', 0.8),
(1, 8, 1, 'prediction', 0.8);

-- ============================================================================
-- Sample Data: Portuguese MWEs
-- ============================================================================

INSERT INTO parser_mwe (idMWE, idGrammarGraph, phrase, components, semanticType, length) VALUES
(1, 1, 'café da manhã', JSON_ARRAY('café', 'da', 'manhã'), 'E', 3),
(2, 1, 'café da tarde', JSON_ARRAY('café', 'da', 'tarde'), 'E', 3),
(3, 1, 'mesa de café da manhã', JSON_ARRAY('mesa', 'de', 'café', 'da', 'manhã'), 'E', 5),
(4, 1, 'pé de moleque', JSON_ARRAY('pé', 'de', 'moleque'), 'E', 3),
(5, 1, 'fim de semana', JSON_ARRAY('fim', 'de', 'semana'), 'E', 3);

-- Create grammar nodes for MWEs (full phrases)
INSERT INTO parser_grammar_node (idGrammarGraph, label, type, threshold) VALUES
(1, 'café da manhã', 'MWE', 3),
(1, 'café da tarde', 'MWE', 3),
(1, 'mesa de café da manhã', 'MWE', 5),
(1, 'pé de moleque', 'MWE', 3),
(1, 'fim de semana', 'MWE', 3);

-- ============================================================================
-- Sample Data: Test Parse Graphs
-- ============================================================================

-- Test 1: Simple sentence "Café está quente" (Coffee is hot)
INSERT INTO parser_graph (idParserGraph, idGrammarGraph, sentence, status) VALUES
(1, 1, 'Café está quente', 'complete');

INSERT INTO parser_node (idParserGraph, label, type, threshold, activation, isFocus, positionInSentence) VALUES
(1, 'café', 'E', 1, 1, TRUE, 1),
(1, 'está', 'R', 1, 1, TRUE, 2),
(1, 'quente', 'A', 1, 1, TRUE, 3);

INSERT INTO parser_link (idParserGraph, idSourceNode, idTargetNode, linkType) VALUES
(1, 1, 2, 'dependency'),  -- café -> está
(1, 2, 3, 'dependency');  -- está -> quente

-- Test 2: MWE test "Tomei café da manhã" (I had breakfast)
INSERT INTO parser_graph (idParserGraph, idGrammarGraph, sentence, status) VALUES
(2, 1, 'Tomei café da manhã', 'complete');

INSERT INTO parser_node (idParserGraph, label, type, threshold, activation, isFocus, positionInSentence, idMWE) VALUES
(2, 'tomei', 'R', 1, 1, TRUE, 1, NULL),
(2, 'café da manhã', 'MWE', 3, 3, TRUE, 2, 1);

INSERT INTO parser_link (idParserGraph, idSourceNode, idTargetNode, linkType) VALUES
(2, 4, 5, 'dependency');  -- tomei -> café da manhã

-- Test 3: Nested MWE "Mesa de café da manhã" (Breakfast table)
INSERT INTO parser_graph (idParserGraph, idGrammarGraph, sentence, status) VALUES
(3, 1, 'Mesa de café da manhã', 'complete');

INSERT INTO parser_node (idParserGraph, label, type, threshold, activation, isFocus, positionInSentence, idMWE) VALUES
(3, 'mesa de café da manhã', 'MWE', 5, 5, TRUE, 1, 3);

-- Test 4: Failed parse example "Café quente da manhã" (interrupted MWE)
INSERT INTO parser_graph (idParserGraph, idGrammarGraph, sentence, status) VALUES
(4, 1, 'Café quente da manhã', 'complete');

INSERT INTO parser_node (idParserGraph, label, type, threshold, activation, isFocus, positionInSentence, idMWE) VALUES
(4, 'café', 'E', 1, 1, TRUE, 1, NULL),
(4, 'quente', 'A', 1, 1, TRUE, 2, NULL),
(4, 'da', 'F', 1, 1, TRUE, 3, NULL),
(4, 'manhã', 'E', 1, 1, TRUE, 4, NULL);

INSERT INTO parser_link (idParserGraph, idSourceNode, idTargetNode, linkType) VALUES
(4, 7, 8, 'dependency'),  -- café -> quente
(4, 7, 9, 'dependency'),  -- café -> da
(4, 9, 10, 'dependency');  -- da -> manhã

-- ============================================================================
-- Utility Views
-- ============================================================================

-- View for complete grammar graph structure
CREATE OR REPLACE VIEW view_parser_grammar_graph AS
SELECT
    gg.idGrammarGraph,
    gg.name,
    gg.language,
    gg.description,
    COUNT(DISTINCT gn.idGrammarNode) AS nodeCount,
    COUNT(DISTINCT gl.idGrammarLink) AS linkCount,
    COUNT(DISTINCT m.idMWE) AS mweCount
FROM parser_grammar_graph gg
LEFT JOIN parser_grammar_node gn ON gg.idGrammarGraph = gn.idGrammarGraph
LEFT JOIN parser_grammar_link gl ON gg.idGrammarGraph = gl.idGrammarGraph
LEFT JOIN parser_mwe m ON gg.idGrammarGraph = m.idGrammarGraph
GROUP BY gg.idGrammarGraph, gg.name, gg.language, gg.description;

-- View for parse graph statistics
CREATE OR REPLACE VIEW view_parser_graph_stats AS
SELECT
    pg.idParserGraph,
    pg.sentence,
    pg.status,
    gg.name AS grammarName,
    COUNT(DISTINCT pn.idParserNode) AS nodeCount,
    COUNT(DISTINCT pl.idParserLink) AS linkCount,
    SUM(CASE WHEN pn.isFocus = TRUE THEN 1 ELSE 0 END) AS focusNodeCount,
    SUM(CASE WHEN pn.type = 'MWE' THEN 1 ELSE 0 END) AS mweNodeCount
FROM parser_graph pg
LEFT JOIN parser_grammar_graph gg ON pg.idGrammarGraph = gg.idGrammarGraph
LEFT JOIN parser_node pn ON pg.idParserGraph = pn.idParserGraph
LEFT JOIN parser_link pl ON pg.idParserGraph = pl.idParserGraph
GROUP BY pg.idParserGraph, pg.sentence, pg.status, gg.name;

-- ============================================================================
-- End of Schema
-- ============================================================================
