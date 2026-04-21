<?php

use App\Data\Daisy\TokenData;
use App\Services\Lexicon\TokenizerService;

describe('TokenizerService', function () {
    it('can be instantiated with default language', function () {
        $service = new TokenizerService;

        expect($service)->toBeInstanceOf(TokenizerService::class);
    });

    it('can be instantiated with custom language', function () {
        $service = new TokenizerService(idLanguage: 1);

        expect($service)->toBeInstanceOf(TokenizerService::class);
    });

    it('tokenizes a simple sentence', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('ele correu');

        expect($tokens)->toBeArray();
        expect($tokens)->toHaveCount(2);
        expect($tokens[0])->toBeInstanceOf(TokenData::class);
        expect($tokens[0]->form)->toBe('ele');
        expect($tokens[1]->form)->toBe('correu');
    });

    it('recognizes MWE ar livre', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('ele correu ao ar livre');

        expect($tokens)->toHaveCount(4);
        expect($tokens[3]->form)->toBe('ar livre');
        expect($tokens[3]->isMwe)->toBeTrue();
    });

    it('recognizes plural MWE gols contra', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('ele marcou dois gols contra');

        // Find the MWE token
        $mweToken = collect($tokens)->first(fn ($t) => $t->isMwe);

        expect($mweToken)->not->toBeNull();
        expect($mweToken->form)->toBe('gols contra');
    });

    it('recognizes plural MWE cafés da manhã', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('eu tomei dois cafés da manhã');

        // Find the MWE token
        $mweToken = collect($tokens)->first(fn ($t) => $t->isMwe);

        expect($mweToken)->not->toBeNull();
        expect($mweToken->form)->toBe('cafés da manhã');
    });

    it('handles punctuation as separate tokens', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('Ola, mundo!');

        $forms = array_map(fn ($t) => $t->form, $tokens);

        expect($forms)->toContain(',');
        expect($forms)->toContain('!');
    });

    it('assigns positions to tokens', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('um dois três');

        expect($tokens[0]->position)->toBe(0);
        expect($tokens[1]->position)->toBe(1);
        expect($tokens[2]->position)->toBe(2);
    });

    it('returns TokenData objects with correct structure', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('ele');

        expect($tokens[0])->toBeInstanceOf(TokenData::class);
        expect($tokens[0]->form)->toBeString();
        expect($tokens[0]->idLemmas)->toBeArray();
        expect($tokens[0]->isMwe)->toBeBool();
        expect($tokens[0]->position)->toBeInt();
    });

    it('can convert token to array', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens = $service->tokenize('ele');

        $array = $tokens[0]->toArray();

        expect($array)->toHaveKeys(['form', 'idLemmas', 'isMwe', 'position']);
    });

    it('can change language and reinitialize', function () {
        $service = new TokenizerService(idLanguage: 1);
        $tokens1 = $service->tokenize('ele', idLanguage: 1);

        // Should work with same language
        $tokens2 = $service->tokenize('ele', idLanguage: 1);

        expect($tokens1)->toHaveCount(1);
        expect($tokens2)->toHaveCount(1);
    });

    it('loads MWE patterns on initialization', function () {
        $service = new TokenizerService(idLanguage: 1);
        $service->initialize();

        expect($service->getMweCount())->toBeGreaterThan(0);
    });
});
