<?php

namespace App\Services\ApiResponse;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  StatusCode  –  Application-level status code constants                 │
 * │                                                                         │
 * │  Numeric string codes that travel alongside the HTTP status code so     │
 * │  clients can switch on a specific domain scenario without text-matching.│
 * │                                                                         │
 * │  Ranges:                                                                │
 * │    1xxx – Success                                                       │
 * │    2xxx – Auth / Account                                                │
 * │    3xxx – Validation / Input                                            │
 * │    4xxx – Resource                                                      │
 * │    5xxx – Payment                                                       │
 * │    9xxx – Server                                                        │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
final class StatusCode
{
    // ── Success (1xxx) ────────────────────────────────────────────────────
    const SUCCESS             = '1000';

    // ── Auth / Account (2xxx) ─────────────────────────────────────────────
    const NOT_VERIFIED        = '2000';
    const NOT_ACTIVE          = '2001';
    const UNAUTHORIZED        = '2002';
    const FORBIDDEN           = '2003';
    const TOKEN_EXPIRED       = '2004';
    const TOKEN_INVALID       = '2005';

    // ── Validation / Input (3xxx) ─────────────────────────────────────────
    const VALIDATION_ERROR    = '3000';
    const INVALID_INPUT       = '3001';

    // ── Resource (4xxx) ───────────────────────────────────────────────────
    const NOT_FOUND           = '4000';
    const ALREADY_EXISTS      = '4001';
    const CONFLICT            = '4002';

    // ── Payment (5xxx) ────────────────────────────────────────────────────
    const PAYMENT_FAILED      = '5000';
    const PAYMENT_GATEWAY_ERROR = '5001';
    const INSUFFICIENT_FUNDS  = '5002';

    // ── Subscription (6xxx) ───────────────────────────────────────────────
    const ALREADY_SUBSCRIBED      = '6000';
    const SUBSCRIPTION_EXPIRED    = '6001';
    const SUBSCRIPTION_CANCELLED  = '6002';
    const PACKAGE_NOT_AVAILABLE   = '6003';
    const SUBSCRIPTION_QUOTA_EXCEEDED = '6004';

    // ── Server (9xxx) ────────────────────────────────────────────────────
    const SERVER_ERROR        = '9000';
    const SERVICE_UNAVAILABLE = '9001';
}
