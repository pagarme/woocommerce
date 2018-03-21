while ! /etc/init.d/mysql status | grep -m1 'is running'; do
    sleep 1
    echo "Waiting for mysql service..."
done