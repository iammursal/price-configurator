#!/usr/bin/env bash
# Setup PHP, Composer commands and detect Herd

PHP_CMD="php"
COMPOSER_CMD="composer"
NPM_CMD="npm"
IS_HERD=false

if $IS_MAC && [ -d "/Applications/Herd.app" ]; then
    if command -v herd >/dev/null 2>&1; then
        IS_HERD=true
        PHP_CMD="herd php"
        COMPOSER_CMD="herd composer"
    else
        echo "⚠️ Warning: Herd app found but 'herd' CLI not in PATH."
    fi
fi

if $IS_WINDOWS; then
    USER_NAME="${USER:-$(whoami)}"
    HERD_PATH="/c/Users/$USER_NAME/.config/herd/bin/herd.bat"
    if [ -f "$HERD_PATH" ]; then
        IS_HERD=true
        COMPOSER_CMD=( "$HERD_PATH" composer )
        PHP_CMD=( "$HERD_PATH" php )
    else
        echo "⚠️ Warning: Herd CLI not found at $HERD_PATH"
    fi
fi

# Unified command runners
run_php() {
    if [ "$IS_WINDOWS" = true ] && [ "$IS_HERD" = true ]; then
        "${PHP_CMD[@]}" "$@"
    else
        $PHP_CMD "$@"
    fi
}

run_composer() {
    if [ "$IS_WINDOWS" = true ] && [ "$IS_HERD" = true ]; then
        "${COMPOSER_CMD[@]}" "$@"
    else
        $COMPOSER_CMD "$@"
    fi
}

run_pkg_manager() {
    # If package manager already chosen, use it
    if [ -n "$CHOSEN_PM" ]; then
        "$CHOSEN_PM" "$@"
        return
    fi

    # List all supported package managers
    OPTIONS=("npm" "yarn" "pnpm" "bun")

    # Detect default from lock files
    if [ -f "pnpm-lock.yaml" ]; then
        DEFAULT_PM="pnpm"
    elif [ -f "yarn.lock" ]; then
        DEFAULT_PM="yarn"
    elif [ -f "bun.lockb" ]; then
        DEFAULT_PM="bun"
    elif [ -f "package-lock.json" ]; then
        DEFAULT_PM="npm"
    else
        DEFAULT_PM="npm" # Fallback default
    fi

    # Print menu
    echo "Available package managers:"
    for i in "${!OPTIONS[@]}"; do
        INDEX=$((i + 1))
        if [ "${OPTIONS[$i]}" == "$DEFAULT_PM" ]; then
            echo "  $INDEX) ${OPTIONS[$i]} (default)"
        else
            echo "  $INDEX) ${OPTIONS[$i]}"
        fi
    done

    # Reprompt loop for valid choice
    while true; do
        read -p "Choose package manager [1-${#OPTIONS[@]}] (default: $DEFAULT_PM): " CHOICE
        CHOICE=${CHOICE:-$(
            for i in "${!OPTIONS[@]}"; do
                if [ "${OPTIONS[$i]}" == "$DEFAULT_PM" ]; then
                    echo $((i + 1))
                    break
                fi
            done
        )}

        # Validate choice
        if [[ "$CHOICE" =~ ^[0-9]+$ ]] && ((CHOICE >= 1 && CHOICE <= ${#OPTIONS[@]})); then
            CHOSEN_PM="${OPTIONS[$((CHOICE - 1))]}"
            break
        else
            echo "Invalid choice. Please select a valid package manager."
        fi
    done

    echo "Using: $CHOSEN_PM"

    # Run with the chosen package manager
    "$CHOSEN_PM" "$@"
}

export PHP_CMD COMPOSER_CMD IS_HERD run_php run_composer
