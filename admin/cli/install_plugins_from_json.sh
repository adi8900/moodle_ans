#!/bin/bash

# Ścieżka do Moodle
MOODLE_DIR="/var/www/html/moodle"

# Uruchomienie komendy uninstall_plugins.php --show-contrib
OUTPUT=$(sudo -u www-data /usr/bin/php "$MOODLE_DIR/admin/cli/uninstall_plugins.php" --show-contrib)

# Sprawdzanie czy komenda zwróciła jakiekolwiek wyniki
if [ -z "$OUTPUT" ]; then
    echo "Brak wyników z polecenia uninstall_plugins.php"
    exit 1
fi

# Tworzenie pustej tablicy do JSON
PLUGIN_DATA=()

# Przetwarzanie wyników komendy
while IFS= read -r line; do
    # Podziel wynik na nazwę wtyczki i opis
    PLUGIN_NAME=$(echo "$line" | awk '{print $1}')
    PLUGIN_DESC=$(echo "$line" | awk '{$1=""; print substr($0,2)}')

    # Dodanie wtyczki do tablicy JSON
    PLUGIN_DATA+=("{\"pluginname\": \"$PLUGIN_NAME\", \"plugin_description\": \"$PLUGIN_DESC\"}")
done <<< "$OUTPUT"

# Zapisanie danych do pliku JSON
echo "[${PLUGIN_DATA[*]}]" | sed 's/ },/},/g' > "$MOODLE_DIR/local/list_user_plugins/user_plugins.json"

echo "Dane o wtyczkach zapisane do pliku user_plugins.json"
