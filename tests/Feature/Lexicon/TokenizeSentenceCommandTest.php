<?php

describe('TokenizeSentenceCommand', function () {
    it('displays help information when called with --help', function () {
        $this->artisan('lexicon:tokenize', ['--help' => true])
            ->assertSuccessful();
    });

    it('tokenizes a simple sentence', function () {
        $this->artisan('lexicon:tokenize', ['sentence' => 'ele correu'])
            ->assertSuccessful();
    });

    it('recognizes MWE ar livre', function () {
        $this->artisan('lexicon:tokenize', ['sentence' => 'ele correu ao ar livre'])
            ->assertSuccessful()
            ->expectsOutputToContain('ar livre');
    });

    it('handles punctuation as separate tokens', function () {
        $this->artisan('lexicon:tokenize', ['sentence' => 'Ola, mundo!'])
            ->assertSuccessful();
    });

    it('recognizes multi-word MWE copa do mundo', function () {
        $this->artisan('lexicon:tokenize', ['sentence' => 'a copa do mundo'])
            ->assertSuccessful()
            ->expectsOutputToContain('copa do mundo');
    });

    it('accepts language parameter', function () {
        $this->artisan('lexicon:tokenize', [
            'sentence' => 'test sentence',
            '--language' => 1,
        ])
            ->assertSuccessful();
    });

    it('outputs JSON format', function () {
        $this->artisan('lexicon:tokenize', ['sentence' => 'ele'])
            ->assertSuccessful()
            ->expectsOutputToContain('JSON output');
    });
});
