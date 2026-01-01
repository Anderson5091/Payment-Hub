class Wallet extends Model
{
    protected $fillable = [
        'operator',
        'dest_wallet_number',
        'dest_wallet_name',
        'is_default',
        'active'
    ];
}
