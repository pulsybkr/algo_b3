"use client";

import { useState, useEffect } from "react";
import { Search, Plus, Library, History } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";

interface Book {
  id: number;
  nom: string;
  description: string;
  disponible: number;
}

export default function Home() {
  const [books, setBooks] = useState<Book[]>([]);
  const [searchColumn, setSearchColumn] = useState("nom");
  const [searchValue, setSearchValue] = useState("");
  const [sortColumn, setSortColumn] = useState("nom");
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [showEditDialog, setShowEditDialog] = useState(false);
  const [selectedBook, setSelectedBook] = useState<Book | null>(null);
  const [newBook, setNewBook] = useState({
    nom: "",
    description: "",
    disponible: 1,
  });
  const [history, setHistory] = useState<string>("");
  const [showHistoryDialog, setShowHistoryDialog] = useState(false);

  const fetchBooks = async () => {
    const response = await fetch("http://localhost:8000/livres");
    if (!response.ok) {
      console.error("Erreur lors de la récupération des livres");
      console.error(response);
      return;
    }
    console.log(response);
    const data = await response.json();
    console.log(data);
    setBooks(data);
  };

  const handleSort = async (column: string) => {
    setSortColumn(column);
    const response = await fetch(`http://localhost:8000/trier?colonne=${column}`);
    const data = await response.json();
    setBooks(data);
  };

  const handleSearch = async () => {
    const response = await fetch(
      `http://localhost:8000/rechercher?colonne=${searchColumn}&valeur=${searchValue}`
    );
    const data = await response.json();
    if (data.success) {
      setBooks(data.resultats);
    }
  };

  const handleAdd = async () => {
    const response = await fetch("http://localhost:8000/ajouter", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newBook),
    });
    const data = await response.json();
    if (response.ok) {
      setShowAddDialog(false);
      setNewBook({ nom: "", description: "", disponible: 1 });
      fetchBooks();
    }
  };

  const handleEdit = async () => {
    if (!selectedBook) return;
    const response = await fetch("http://localhost:8000/modifier", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(selectedBook),
    });
    const data = await response.json();
    if (response.ok) {
      setShowEditDialog(false);
      setSelectedBook(null);
      fetchBooks();
    }
  };

  const handleDelete = async (id: number) => {
    const response = await fetch("http://localhost:8000/supprimer", {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id }),
    });
    const data = await response.json();
    if (response.ok) {
      fetchBooks();
    }
  };

  useEffect(() => {
    fetchBooks();
  }, []);

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto py-8 px-4">
        <div className="flex items-center justify-between mb-8">
          <div className="flex items-center gap-2">
            <Library className="h-8 w-8" />
            <h1 className="text-2xl font-bold">Bibliothèque</h1>
          </div>
          <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
            <DialogTrigger asChild>
              <Button>
                <Plus className="mr-2 h-4 w-4" />
                Ajouter un livre
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Ajouter un nouveau livre</DialogTitle>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Nom</Label>
                  <Input
                    value={newBook.nom}
                    onChange={(e) => setNewBook({ ...newBook, nom: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Description</Label>
                  <Input
                    value={newBook.description}
                    onChange={(e) => setNewBook({ ...newBook, description: e.target.value })}
                  />
                </div>
                <div className="flex items-center space-x-2">
                  <Switch
                    checked={newBook.disponible === 1}
                    onCheckedChange={(checked) =>
                      setNewBook({ ...newBook, disponible: checked ? 1 : 0 })
                    }
                  />
                  <Label>Disponible</Label>
                </div>
                <Button onClick={handleAdd} className="w-full">
                  Ajouter
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
          <Card>
            <CardHeader>
              <CardTitle>Rechercher</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <Select
                value={searchColumn}
                onValueChange={(value) => setSearchColumn(value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Colonne" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="nom">Nom</SelectItem>
                  <SelectItem value="description">Description</SelectItem>
                  <SelectItem value="disponible">Disponibilité</SelectItem>
                </SelectContent>
              </Select>
              <div className="flex space-x-2">
                <Input
                  placeholder="Rechercher..."
                  value={searchValue}
                  onChange={(e) => setSearchValue(e.target.value)}
                />
                <Button onClick={handleSearch}>
                  <Search className="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Trier par</CardTitle>
            </CardHeader>
            <CardContent>
              <Select value={sortColumn} onValueChange={handleSort}>
                <SelectTrigger>
                  <SelectValue placeholder="Colonne" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="nom">Nom</SelectItem>
                  <SelectItem value="description">Description</SelectItem>
                  <SelectItem value="disponible">Disponibilité</SelectItem>
                </SelectContent>
              </Select>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Historique</CardTitle>
            </CardHeader>
            <CardContent>
              <Button variant="outline" className="w-full" onClick={async () => {
                const response = await fetch("http://localhost:8000/historique");
                const data = await response.json();
                setHistory(data.historique);
                setShowHistoryDialog(true);
              }}>
                <History className="mr-2 h-4 w-4" />
                Voir l&apos;historique
              </Button>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {books.map((book) => (
            <Card key={book.id}>
              <CardHeader>
                <CardTitle>{book.nom}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-gray-500 mb-4">{book.description}</p>
                <div className="flex items-center justify-between">
                  <span
                    className={`px-2 py-1 rounded-full text-xs ${
                      book.disponible == 1
                        ? "bg-green-100 text-green-800"
                        : "bg-red-100 text-red-800"
                    }`}
                  >
                    {book.disponible == 1 ? "Disponible" : "Indisponible"}
                  </span>
                  <div className="space-x-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        setSelectedBook(book);
                        setShowEditDialog(true);
                      }}
                    >
                      Modifier
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleDelete(book.id)}
                    >
                      Supprimer
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Modifier le livre</DialogTitle>
            </DialogHeader>
            {selectedBook && (
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Nom</Label>
                  <Input
                    value={selectedBook.nom}
                    onChange={(e) =>
                      setSelectedBook({ ...selectedBook, nom: e.target.value })
                    }
                  />
                </div>
                <div className="space-y-2">
                  <Label>Description</Label>
                  <Input
                    value={selectedBook.description}
                    onChange={(e) =>
                      setSelectedBook({ ...selectedBook, description: e.target.value })
                    }
                  />
                </div>
                <div className="flex items-center space-x-2">
                  <Switch
                    checked={selectedBook.disponible == 1}
                    onCheckedChange={(checked) =>
                      setSelectedBook({
                        ...selectedBook,
                        disponible: checked ? 1 : 0,
                      })
                    }
                  />
                  <Label>Disponible</Label>
                </div>
                <Button onClick={handleEdit} className="w-full">
                  Enregistrer
                </Button>
              </div>
            )}
          </DialogContent>
        </Dialog>

        <Dialog open={showHistoryDialog} onOpenChange={setShowHistoryDialog}>
          <DialogContent className="max-w-3xl max-h-[80vh]">
            <DialogHeader>
              <DialogTitle>Historique des opérations</DialogTitle>
            </DialogHeader>
            <div className="space-y-4 py-4 overflow-y-auto">
              <pre className="whitespace-pre-wrap text-sm scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                {history}
              </pre>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  );
}