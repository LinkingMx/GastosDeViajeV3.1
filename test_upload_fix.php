<?php

echo "=== Testing Deposit Receipt Upload Fix ===\n\n";

// Get a treasury team member
$treasuryUser = App\Models\User::treasuryTeam()->first();
echo "Testing with treasury user: {$treasuryUser->name}\n\n";

// Get a travel request that can have deposits
$request = App\Models\TravelRequest::whereIn('status', ['approved', 'travel_review', 'travel_approved'])
    ->where('advance_deposit_made', false)
    ->first();

if (! $request) {
    echo "No eligible travel requests found.\n";
    exit;
}

echo "Testing with travel request ID: {$request->id}\n";
echo "Status: {$request->status}\n";
echo "User: {$request->user->name}\n\n";

// Check deposit attachment type
$depositType = App\Models\AttachmentType::where('slug', 'advance_deposit_receipt')->first();
echo 'Deposit attachment type: '.($depositType ? "Found (ID: {$depositType->id})" : 'Not found')."\n";

// Check if user can mark deposit
$canMark = $request->canMarkAdvanceDeposit($treasuryUser);
echo 'Can mark advance deposit: '.($canMark ? 'YES' : 'NO')."\n\n";

if ($canMark) {
    echo "✓ Treasury functionality is ready for file upload testing\n";
    echo "✓ You can now test the deposit receipt upload through the Filament UI\n";
} else {
    echo "✗ Cannot mark deposit - check permissions and request status\n";
}

echo "\n=== Test completed ===\n";
