def czy_wzglednie_pierwsza(a, b):
    while b != 0:
        a, b = b, a % b
    return a == 1

def znajdz_dzielniki(n):
    dzielniki = []
    for i in range(1, int(n ** 0.5) + 1):
        if n % i == 0:
            dzielniki.append(i)
            if n // i != i:
                dzielniki.append(n // i)
    return sorted(dzielniki)

def zadanie1_liczby_mniejsze_i_ostatnie(liczby):
    mniejsze = [liczba for liczba in liczby if liczba < 1000]
    ostatnie_dwie = sorted(mniejsze)[-2:]
    return len(mniejsze), ostatnie_dwie

def zadanie2_liczby_z_18_dzielnikami(liczby):
    wyniki = []
    for liczba in liczby:
        dzielniki = znajdz_dzielniki(liczba)
        if len(dzielniki) == 18:
            wyniki.append((liczba, dzielniki))
    return wyniki

def zadanie3_najwieksza_wzglednie_pierwsza(liczby):
    max_wzglednie_pierwsza = 0
    for liczba in liczby:
        wzglednie_pierwsza = True
        for other in liczby:
            if liczba != other and not czy_wzglednie_pierwsza(liczba, other):
                wzglednie_pierwsza = False
                break
        if wzglednie_pierwsza and liczba > max_wzglednie_pierwsza:
            max_wzglednie_pierwsza = liczba
    return max_wzglednie_pierwsza

# Wczytanie liczb z pliku
with open('liczby.txt', 'r') as file:
    liczby = [int(line.strip()) for line in file]

# Zadanie 1
liczba_mniejszych, ostatnie_dwie = zadanie1_liczby_mniejsze_i_ostatnie(liczby)

# Zadanie 2
wyniki_z2 = zadanie2_liczby_z_18_dzielnikami(liczby)

# Zadanie 3
max_wzglednie_pierwsza = zadanie3_najwieksza_wzglednie_pierwsza(liczby)

# Zapisanie wyników do pliku
with open('wyniki.txt', 'w') as file:
    file.write(f"Zadanie 1:\nLiczba liczb mniejszych niz 1000: {liczba_mniejszych}\nOstatnie dwie liczby mniejsze niz 1000: {ostatnie_dwie[0]}, {ostatnie_dwie[1]}\n\n")
    file.write("Zadanie 2:\n")
    for wynik in wyniki_z2:
        file.write(f"Liczba: {wynik[0]}, Dzielniki: {wynik[1]}\n")
    file.write(f"\nZadanie 3:\nNajwieksza liczba wzglednie pierwsza ze wszystkimi pozostalymi: {max_wzglednie_pierwsza}")