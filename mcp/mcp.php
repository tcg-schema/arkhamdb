#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use App\Mcp\ArkhamTools;

$server = Server::builder()
    ->setServerInfo('ArkhamDB MCP Server', '1.0.0')
    ->addTool([ArkhamTools::class, 'search_cards'], 'search_cards', 'Search for Arkham Horror LCG cards by name or traits')
    ->addTool([ArkhamTools::class, 'get_card'], 'get_card', 'Get full details of a card by its code')
    ->build();

$transport = new StdioTransport();
$server->run($transport);
