# SMS Sender ID Requirements

## Problem Solved ✅
Your sender ID "TravelPlanerOTP" was too long and got deleted. SMS providers have strict character limits.

## Sender ID Limits
- **MSG91**: Maximum 6 characters
- **TextLocal**: Maximum 6 characters
- **Most SMS Providers**: 6-11 characters max

## Updated Configuration
Your sender ID is now set to: **TRVLPL** (6 characters)

## How to Set Up Sender ID

### For TextLocal:
1. Go to TextLocal dashboard
2. Navigate to **Sender ID** section
3. Request sender ID: **TRVLPL**
4. Wait for approval (usually 24-48 hours)
5. Once approved, you can use it

### For MSG91:
1. Go to MSG91 dashboard
2. Navigate to **Sender ID** section
3. Request sender ID: **TRVLPL**
4. Wait for approval
5. Once approved, you can use it

## Alternative Sender IDs (if TRVLPL is not available):
- **TRVL** (4 characters)
- **TRAVEL** (6 characters)
- **PLANR** (5 characters)
- **TRIP** (4 characters)

## Important Notes:
1. **Sender IDs are case-sensitive**
2. **Numbers are usually not allowed**
3. **Special characters are not allowed**
4. **Approval process takes time**
5. **Some providers have pre-approved sender IDs**

## Testing Without Custom Sender ID:
If your custom sender ID is not approved yet, you can use:
- **TXTLCL** (TextLocal default)
- **MSG91** (MSG91 default)

## Current Configuration:
```php
$this->sender = 'TRVLPL'; // ✅ 6 characters max
```

## Next Steps:
1. Request sender ID approval from your SMS provider
2. Test SMS functionality with default sender ID first
3. Update configuration once custom sender ID is approved 